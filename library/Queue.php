<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue;

use Stakhanovist\Queue\Adapter\AdapterFactory;
use Stakhanovist\Queue\Adapter\AdapterInterface;
use Stakhanovist\Queue\Adapter\Capabilities\AwaitMessagesCapableInterface;
use Stakhanovist\Queue\Adapter\Capabilities\CountMessagesCapableInterface;
use Stakhanovist\Queue\Adapter\Capabilities\DeleteMessageCapableInterface;
use Stakhanovist\Queue\Exception;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;
use Stakhanovist\Queue\Parameter\SendParameters;
use Stakhanovist\Queue\Parameter\SendParametersInterface;
use Traversable;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\MessageInterface;

/**
 * Class Queue
 */
class Queue implements QueueClientInterface, EventManagerAwareInterface
{
    /**
     * Queue name
     *
     * @var string
     */
    protected $name;

    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var bool
     */
    protected $adapterConnected = false;

    /**
     * User-provided configuration
     *
     * @var QueueOptionsInterface
     */
    protected $options;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Constructor
     *
     * @param  string $name
     * @param  AdapterInterface $adapter
     * @param  QueueOptionsInterface $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name, AdapterInterface $adapter, QueueOptionsInterface $options = null)
    {
        if (empty($name)) {
            throw new Exception\InvalidArgumentException('No valid param $name passed to constructor: cannot be empty');
        }

        $this->name = $name;

        $this->adapter = $adapter;

        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Instantiate a queue
     *
     * @param  array|Traversable $config
     * @return Queue
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        if (!isset($config['name'])) {
            throw new Exception\InvalidArgumentException('Missing "name"');
        }

        /** @var $adapter \Stakhanovist\Queue\Adapter\AdapterInterface */
        if ($config['adapter'] instanceof AdapterInterface) {
            // $config['adapter'] is already an adapter object
            $adapter = $config['adapter'];
        } else {
            $adapter = AdapterFactory::factory($config['adapter']);
        }

        $options = null;
        if (isset($config['options'])) {
            if (!is_array($config['options'])) {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        '"%s" must be an array; "%s" given.',
                        'options',
                        gettype($config['options'])
                    )
                );
            }
            $options = new QueueOptions($config['options']);
        }

        return new static($config['name'], $adapter, $options);
    }

    /**
     * Set options
     *
     * @param  QueueOptionsInterface $options
     * @return Queue
     */
    public function setOptions(QueueOptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return QueueOptionsInterface
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new QueueOptions();
        }
        return $this->options;
    }

    /**
     * Get the queue name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the adapter for this queue
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        // Ensure connection at first using
        if (!$this->adapterConnected) {
            $this->adapterConnected = $this->adapter->connect();
        }

        return $this->adapter;
    }


    /**
     * Ensure that this queue exists
     *
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function ensureQueue()
    {
        $name = $this->getName();
        if ($this->getAdapter()->queueExists($name)) {
            return true;
        }

        return $this->getAdapter()->createQueue($name);
    }

    /**
     * Delete the queue this object is working on.
     *
     * This queue is disabled, regardless of the outcome of the deletion
     * of the queue, because the programmers intent is to disable this queue.
     *
     * @return boolean
     */
    public function deleteQueue()
    {
        $name = $this->getName();
        $adapter = $this->getAdapter();

        $deleted = false;

        if ($adapter->queueExists($name)) {
            $deleted = $adapter->deleteQueue($name);
        }

        /**
         * @see Adapter\Null
         */
        $this->adapter = new Adapter\NullAdapter;

        return $deleted;
    }

    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @param  SendParametersInterface $params
     * @return MessageInterface
     * @throws Exception\ExceptionInterface
     */
    public function send($message, SendParametersInterface $params = null)
    {
        if (!$message instanceof MessageInterface) {
            $data = $message;
            $messageClass = $this->getOptions()->getMessageClass();
            if (is_string($data)) {
                /** @var $message MessageInterface */
                $message = new $messageClass;
                $message->setContent($data);
            } elseif (is_array($data) && isset($data['content'])) {
                /** @var $message MessageInterface */
                $message = new $messageClass;
                $message->setContent((string)$data['content']);
                if (isset($data['metadata'])) {
                    $message->setMetadata($data['metadata']);
                }
            } else {
                throw new Exception\InvalidArgumentException('Invalid $message type');
            }
        }

        return $this->getAdapter()->sendMessage($this, $message, $params);
    }

    /**
     * Return the first element in the queue
     *
     * @param  integer $maxMessages
     * @param  ReceiveParametersInterface $params
     * @return Message\MessageIterator
     * @throws Exception\InvalidArgumentException
     */
    public function receive($maxMessages = 1, ReceiveParametersInterface $params = null)
    {
        if (($maxMessages !== null) && (!is_integer($maxMessages) || $maxMessages < 1)) {
            throw new Exception\InvalidArgumentException('$maxMessages must be an integer greater than 0 or null');
        }

        return $this->getAdapter()->receiveMessages($this, $maxMessages, $params);
    }

    /**
     * Await messages
     *
     * @param  ReceiveParametersInterface $params
     * @return Queue
     * @throws Exception\InvalidArgumentException
     */
    public function await(ReceiveParametersInterface $params = null)
    {
        /* @var $adapter AdapterInterface */
        $adapter = $this->getAdapter();
        $adapterCanAwait = $adapter instanceof AwaitMessagesCapableInterface;

        if (!$adapterCanAwait && !$this->getOptions()->getEnableAwaitEmulation()) {
            throw new Exception\UnsupportedMethodCallException(
                sprintf(
                    '%s() is not supported by "%s" and await emulation is not enabled',
                    __FUNCTION__,
                    get_class($this->getAdapter())
                )
            );
        }

        $eventManager = $this->getEventManager();
        $e = $this->getEvent();
        $callback = function (MessageIterator $iterator) use ($eventManager, $e) {

            $e->stopAwait(false);
            $e->setMessages($iterator);

            if ($iterator->count() > 0) {
                $eventManager->trigger(QueueEvent::EVENT_RECEIVE, $e);
                if ($e->awaitIsStopped()) {
                    return false;
                }
            }

            $eventManager->trigger(QueueEvent::EVENT_IDLE, $e);
            return !$e->awaitIsStopped();
        };

        if ($adapterCanAwait) {
            /* @var $adapter AwaitMessagesCapableInterface */
            $adapter->awaitMessages($this, $callback, $params);
        } else { //else, await emulation (polling)

            $pollingInterval = $this->getOptions()->getPollingInterval();

            do {
                $messages = $adapter->receiveMessages($this, 1, $params);
                $continue = call_user_func($callback, $messages);

                if ($continue && $messages->count() < 1) {
                    sleep($pollingInterval);
                }
            } while ($continue);
        }

        return $this;
    }


    /**
     * Returns the approximate number of messages in the queue
     *
     * @return integer|null
     * @throws Exception\UnsupportedMethodCallException
     */
    public function count()
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof CountMessagesCapableInterface) {
            throw new Exception\UnsupportedMethodCallException(
                sprintf(
                    '%s() is not supported by "%s"',
                    __FUNCTION__,
                    get_class($this->getAdapter())
                )
            );
        }

        return $adapter->countMessages($this);
    }

    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     *
     * @param  MessageInterface $message
     * @return boolean
     * @throws Exception\UnsupportedMethodCallException
     */
    public function delete(MessageInterface $message)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof DeleteMessageCapableInterface) {
            throw new Exception\UnsupportedMethodCallException(
                sprintf(
                    '%s() is not supported by "%s"',
                    __FUNCTION__,
                    get_class($this->getAdapter())
                )
            );
        }

        return $adapter->deleteMessage($this, $message);
    }


    /**
     * Schedule a message to the queue
     *
     * @param  mixed $message message
     * @param  int $scheduleTime
     * @param  int $repeatingInterval
     * @param  SendParametersInterface $params
     * @return MessageInterface
     * @throws Exception\UnsupportedMethodCallException
     */
    public function schedule(
        $message,
        $scheduleTime = null,
        $repeatingInterval = null,
        SendParametersInterface $params = null
    ) {
        if (!$this->isSendParamSupported(SendParametersInterface::SCHEDULE)) {
            throw new Exception\UnsupportedMethodCallException(
                sprintf(
                    '"%s"" param is not supported by "%s"',
                    SendParametersInterface::SCHEDULE,
                    get_class($this->getAdapter())
                )
            );
        }

        if ($repeatingInterval !== null && !$this->isSendParamSupported(SendParametersInterface::REPEATING_INTERVAL)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '"%s"" param is not supported by "%s"',
                    SendParametersInterface::REPEATING_INTERVAL,
                    get_class($this->getAdapter())
                )
            );
        }

        if ($params === null) {
            $params = new SendParameters();
        }

        $params->setSchedule($scheduleTime)
            ->setRepeatingInterval($repeatingInterval);

        return $this->send($message, $params);
    }

    /**
     * Unschedule a message
     *
     * @param MessageInterface $message
     * @throws Exception\UnsupportedMethodCallException
     * @return boolean
     */
    public function unschedule(MessageInterface $message)
    {
        if (!$this->isSendParamSupported(SendParametersInterface::SCHEDULE) || !$this->canDeleteMessage()) {
            throw new Exception\UnsupportedMethodCallException(
                sprintf(
                    '"%s"" param or delete message capabilities are not supported by "%s"',
                    SendParametersInterface::SCHEDULE,
                    get_class($this->getAdapter())
                )
            );
        }

        $info = $this->getAdapter()->getMessageInfo($this, $message);
        $options = &$info['options'];

        if (isset($options[SendParametersInterface::SCHEDULE])) {
            unset($options[SendParametersInterface::SCHEDULE]);
        }

        if (isset($options[SendParametersInterface::REPEATING_INTERVAL])) {
            unset($options[SendParametersInterface::REPEATING_INTERVAL]);
        }

        $message->setMetadata($this->getOptions()->getMessageMetadatumKey(), $info);

        return $this->delete($message);
    }


    /********************************************************************
     * Available Parameters
     *********************************************************************/

    /**
     * @param bool $name
     * @return bool
     */
    public function isSendParamSupported($name)
    {
        return in_array($name, $this->getAdapter()->getAvailableSendParams());
    }

    /**
     * @param bool $name
     * @return bool
     */
    public function isReceiveParamSupported($name)
    {
        return in_array($name, $this->getAdapter()->getAvailableReceiveParams());
    }

    /********************************************************************
     * Capabilities
     *********************************************************************/

    /**
     * Can queue wait for messages?
     *
     * Return true if the adapter is await-capable or enableAwaitEmulation is active.
     *
     * @return bool
     */
    public function canAwait()
    {
        return ($this->getAdapter() instanceof AwaitMessagesCapableInterface) || $this->getOptions(
        )->getEnableAwaitEmulation();
    }

    /**
     * Is queue using await emulations?
     *
     * Return true if the adapter isn't await-capable and enableAwaitEmulation is active.
     *
     * @return bool
     */
    public function isAwaitEmulation()
    {
        return !($this->getAdapter() instanceof AwaitMessagesCapableInterface) && $this->getOptions(
        )->getEnableAwaitEmulation();
    }

    /**
     * Can queue delete message?
     *
     * Return true if the adapter is capable to delete messages.
     *
     * @return bool
     */
    public function canDeleteMessage()
    {
        return $this->getAdapter() instanceof DeleteMessageCapableInterface;
    }

    /**
     * Can count in queue messages?
     *
     * Return true if the adapter can count messages.
     *
     * @return bool
     */
    public function canCountMessages()
    {
        return $this->getAdapter() instanceof CountMessagesCapableInterface;
    }


    /********************************************************************
     * Event
     *********************************************************************/

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return Queue
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(
            [
                __CLASS__,
                get_class($this),
                $this->getName(),
            ]
        );
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Set an event to use during the queue event flow
     *
     * By default, will re-cast to QueueEvent if another event type is provided.
     *
     * @param  Event $e
     * @return Queue
     */
    public function setEvent(Event $e)
    {
        if (!$e instanceof QueueEvent) {
            $eventParams = $e->getParams();
            $e = new QueueEvent();
            $e->setParams($eventParams);
            unset($eventParams);
        }
        $this->event = $e;

        return $this;
    }

    /**
     * Get the attached event
     *
     * Will create a new QueueEvent if none provided.
     *
     * @return QueueEvent
     */
    public function getEvent()
    {
        if (!$this->event) {
            $this->setEvent(new QueueEvent());
        }

        return $this->event;
    }

    /********************************************************************
     * Debug
     *********************************************************************/

    /**
     * returns a listing of Queue details.
     * useful for debugging
     *
     * @return array
     */
    public function debugInfo()
    {
        $info = [];
        $info['self'] = get_called_class();
        $info['adapter'] = get_class($this->getAdapter());
        $info['name'] = $this->getName();
        $info['messageClass'] = $this->getOptions()->getMessageClass();
        $info['messageSetClass'] = $this->getOptions()->getMessageSetClass();

        return $info;
    }
}
