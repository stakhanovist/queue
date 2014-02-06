<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueue;

use Countable;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\MessageInterface;
use ZendQueue\Exception;
use ZendQueue\Adapter\AdapterInterface;
use ZendQueue\Adapter\Capabilities\AwaitMessagesCapableInterface;
use ZendQueue\Adapter\Capabilities\ListQueuesCapableInterface;
use ZendQueue\Adapter\Capabilities\CountMessagesCapableInterface;
use ZendQueue\Adapter\Capabilities\DeleteMessageCapableInterface;
use ZendQueue\Parameter\SendParameters;
use ZendQueue\Parameter\ReceiveParameters;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use ZendQueue\Adapter\AdapterFactory;
use Zend\EventManager\EventManager;
use Zend\Config\Processor\Queue;
use ZendQueue\Message\MessageIterator;

/**
 *
 */
class Queue implements Countable
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
     * @var QueueOptions
     */
    protected $options;

    /**
     * @var string
     */
    protected $eventIdentifier;

    /**
     * Constructor
     *
     * @param  string $name
     * @param  AdapterInterface $adapter
     * @param  QueueOptions $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name, AdapterInterface $adapter, QueueOptions $options = null)
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
     * @param  array|Traversable $cfg
     * @return Queue
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($cfg)
    {
        if ($cfg instanceof Traversable) {
            $cfg = ArrayUtils::iteratorToArray($cfg);
        }

        if (!is_array($cfg)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        if (!isset($cfg['name'])) {
            throw new Exception\InvalidArgumentException('Missing "name"');
        }

        if ($cfg['adapter'] instanceof AdapterInterface) {
            // $cfg['adapter'] is already an adapter object
            $adapter = $cfg['adapter'];
        } else {
            $adapter = AdapterFactory::factory($cfg['adapter']);
        }

        $options = null;
        if (isset($cfg['options'])) {
            if (!is_array($cfg['options'])) {
                throw new Exception\InvalidArgumentException('"options" must be an array, ' . gettype($cfg['options']) . ' given.');
            }
            $options = new QueueOptions($cfg['options']);
        }

        return new static($cfg['name'], $adapter, $options);
    }

    /**
     * Set options
     *
     * @param  QueueOptions $options
     * @return Queue
     */
    public function setOptions(QueueOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return QueueOptions
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new QueueOptions();
        }
        return $this->options;
    }

    /**
     * Get Queue name
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
        //Ensure connection at first using
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
        if($this->getAdapter()->queueExists($name)) {
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

        if($adapter->queueExists($name)) {
            $deleted = $adapter->deleteQueue($name);
        }

        /**
         * @see Adapter\Null
         */
        $this->adapter = new Adapter\Null();

        return $deleted;
    }

    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @param  SendParamters $params
     * @return MessageInterface
     * @throws Exception\ExceptionInterface
     */
    public function send($message, SendParameters $params = null)
    {
        if (!($message instanceof MessageInterface)) {
            $data = $message;
            $messageClass = $this->getOptions()->getMessageClass();
            if (is_string($data)) {
                $message = new $messageClass;
                $message->setContent($data);
            } else if(is_array($data) && isset($data['content'])) {
                $message = new $messageClass;
                $message->setContent((string) $data['content']);
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
     * @param  ReceiveParameters $params
     * @return Message\MessageIterator
     * @throws Exception\InvalidArgumentException
     */
    public function receive($maxMessages = 1, ReceiveParameters $params = null)
    {
        if (($maxMessages !== null) && (!is_integer($maxMessages) || $maxMessages < 1)) {
            throw new Exception\InvalidArgumentException('$maxMessages must be an integer greater than 0 or null');
        }

        return $this->getAdapter()->receiveMessages($this, $maxMessages, $params);
    }

    /**
     * Await messages
     *
     * @param  ReceiveParameters $params
     * @return MessageInterface
     * @throws Exception\InvalidArgumentException
     */
    public function await(ReceiveParameters $params = null)
    {

        $e = $this->getEvent();
        $enablePolling = !$this->getAdapter() instanceof AwaitMessagesCapableInterface;

        if ($enablePolling && !$this->getOptions()->getEnableAwaitEmulation()) {
            throw new Exception\UnsupportedMethodCallException(__FUNCTION__ . '() is not supported by ' . get_class($this->getAdapter()) . ' and await emulation is not enabled.');
        }

        do {

            $messages = null;
            if ($enablePolling) {
                $messages = $this->getAdapter()->receiveMessages($this, 1, $params);

                if (!$messages->count()) {
                    sleep($this->getOptions()->getPollingInterval());
                }

            } else {
                $message = $this->getAdapter()->awaitMessage($this, $params);
                $messages = new MessageIterator($message ? array($message) : array(), $this);
            }

            $e->setMessages($messages);

            $result = $this->getEventManager()->trigger($messages->count() ? QueueEvent::EVENT_RECEIVE : QueueEvent::EVENT_IDLE, $e);

        } while(!$result->stopped());

        return $message;
    }


    /**
     * Returns the approximate number of messages in the queue
     *
     * @return integer|null
     * @throws Exception\UnsupportedMethodCallException
     */
    public function count()
    {
        if (!$this->canCountMessages()) {
            throw new Exception\UnsupportedMethodCallException(__FUNCTION__ . '() is not supported by ' . get_class($this->getAdapter()));
        }

        return $this->getAdapter()->countMessages($this);
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
    public function deleteMessage(MessageInterface $message)
    {
        if (!$this->canDeleteMessage()) {
            throw new Exception\UnsupportedMethodCallException(__FUNCTION__ . '() is not supported by ' . get_class($this->getAdapter()));
        }

        return $this->getAdapter()->deleteMessage($this, $message);
    }


    /**
     * Schedule a message to the queue
     *
     * @param  mixed $message message
     * @param  int $scheduleTime
     * @param  int $repeatingInterval
     * @param  SendParamters $params
     * @return MessageInterface
     * @throws Exception\UnsupportedMethodCallException
     */
    public function schedule($message, $scheduleTime = null, $repeatingInterval = null, SendParameters $params = null)
    {
        if (!$this->isSendParamSupported(SendParameters::SCHEDULE)) {
            throw new Exception\UnsupportedMethodCallException('\''.SendParameters::SCHEDULE.'\' param is not supported by ' . get_class($this->getAdapter()));
        }

        if ($interval !== null && !$this->isSendParamSupported(SendParameters::REPEATING_INTERVAL)) {
            if (!$this->isSendParamSupported(SendParameters::REPEATING_INTERVAL)) {
                throw new Exception\UnsupportedMethodCallException('\''.SendParameters::REPEATING_INTERVAL.'\' param is not supported by ' . get_class($this->getAdapter()));
            }

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
        if (!$this->isSendParamSupported(SendParameters::SCHEDULE)) {
            throw new Exception\UnsupportedMethodCallException('\''.SendParameters::SCHEDULE.'\' param is not supported by ' . get_class($this->getAdapter()));
        }

        $info = $this->getAdapter()->getMessageInfo($this, $message);

        $options = $info['options'];

        if (isset($options[SendParameters::SCHEDULE])) {
            unset($options[SendParameters::SCHEDULE]);
        }

        if (isset($options[SendParameters::REPEATING_INTERVAL])) {
            unset($options[SendParameters::REPEATING_INTERVAL]);
        }

        $info['options'] = $options;

        $message->setMetadata($queue->getOptions()->getMessageMetadatumKey(), $options);

        return $this->deleteMessage($message);
    }


    /********************************************************************
     * Available Parameters
    *********************************************************************/

    public function isSendParamSupported($name)
    {
        return in_array($name, $this->getAdapter()->getAvailableSendParams());
    }

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
        return ($this->getAdapter() instanceof AwaitMessagesCapableInterface) || $this->getOptions()->getEnableAwaitEmulation();
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
        return !($this->getAdapter() instanceof AwaitMessagesCapableInterface) && $this->getOptions()->getEnableAwaitEmulation();
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



    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return Queue
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            'Zend\Stdlib\DispatchableInterface',
            __CLASS__,
            get_class($this),
            $this->eventIdentifier,
            substr(get_class($this), 0, strpos(get_class($this), '\\'))
        ));
        $this->events = $events;
//         $this->attachDefaultListeners();

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
     * @return void
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


    /**
     * returns a listing of Queue details.
     * useful for debugging
     *
     * @return array
     */
    public function debugInfo()
    {
        $info = array();
        $info['self']               = get_called_class();
        $info['adapter']            = get_class($this->getAdapter());
        $info['name']               = $this->getName();
        $info['messageClass']       = $this->getOptions()->getMessageClass();
        $info['messageSetClass']    = $this->getOptions()->getMessageSetClass();

        return $info;
    }



}