<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Adapter;

use Stakhanovist\Queue\Adapter\Capabilities\CountMessagesCapableInterface;
use Stakhanovist\Queue\Adapter\Capabilities\DeleteMessageCapableInterface;
use Stakhanovist\Queue\Adapter\Capabilities\ListQueuesCapableInterface;
use Stakhanovist\Queue\Exception;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;
use Stakhanovist\Queue\Parameter\SendParametersInterface;
use Stakhanovist\Queue\QueueInterface;
use Zend\Math\Rand;
use Zend\Stdlib\MessageInterface;

/**
 * Class for using a standard PHP array as a queue
 *
 */
class ArrayAdapter extends AbstractAdapter implements
    DeleteMessageCapableInterface,
    ListQueuesCapableInterface,
    CountMessagesCapableInterface
{
    /**
     * @var array
     */
    protected $data = [];


    /**
     * List avaliable params for sendMessage()
     *
     * @return array
     */
    public function getAvailableSendParams()
    {
        return [
            SendParametersInterface::SCHEDULE,
            SendParametersInterface::REPEATING_INTERVAL,
        ];
    }

    /**
     * List avaliable params for receiveMessages()
     *
     * @return array
     */
    public function getAvailableReceiveParams()
    {
        return [
            ReceiveParametersInterface::VISIBILITY_TIMEOUT,
            ReceiveParametersInterface::CLASS_FILTER,
            ReceiveParametersInterface::PEEK_MODE,
        ];
    }


    /********************************************************************
     * Queue management functions
     *********************************************************************/

    /**
     * Ensure connection
     *
     * Dummy method - ArrayAdapter needs no connection
     *
     * @return boolean
     */
    public function connect()
    {
        return true;
    }

    /**
     * Returns the ID of the queue
     *
     * @param string $name Queue name
     * @return string
     * @throws Exception\QueueNotFoundException
     */
    public function getQueueId($name)
    {
        if ($this->queueExists($name)) {
            return $name;
        }

        throw new Exception\QueueNotFoundException('Queue does not exist: ' . $name);
    }

    /**
     * Check if a queue exists
     *
     * @param string $name
     * @return boolean
     */
    public function queueExists($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Create a new queue
     *
     * @param string $name queue name
     * @return boolean
     */
    public function createQueue($name)
    {
        if ($this->queueExists($name)) {
            return false;
        }

        $this->data[$name] = [];

        return true;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * Returns false if the queue is not found, true if the queue exists
     *
     * @param string $name queue name
     * @return boolean
     */
    public function deleteQueue($name)
    {
        $found = isset($this->data[$name]);

        if ($found) {
            unset($this->data[$name]);
        }

        return $found;
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     */
    public function listQueues()
    {
        return array_keys($this->data);
    }

    /**
     * Return the approximate number of messages in the queue
     *
     * @param QueueInterface $queue
     * @return integer
     * @throws Exception\QueueNotFoundException
     */
    public function countMessages(QueueInterface $queue)
    {
        if (!isset($this->data[$queue->getName()])) {
            throw new Exception\QueueNotFoundException('Queue does not exist');
        }

        return count($this->data[$queue->getName()]);
    }

    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @param SendParametersInterface $params
     * @return MessageInterface
     * @throws Exception\QueueNotFoundException
     */
    public function sendMessage(
        QueueInterface $queue,
        MessageInterface $message,
        SendParametersInterface $params = null
    ) {
        if (!$this->queueExists($queue->getName())) {
            throw new Exception\QueueNotFoundException('Queue does not exist: ' . $queue->getName());
        }

        $this->cleanMessageInfo($queue, $message);

        $msg = [
            'created' => time(),
            'class' => get_class($message),
            'content' => (string)$message->getContent(),
            'metadata' => $message->getMetadata(),
            'handle' => null,
        ];

        if ($params) {
            if ($params->getSchedule()) {
                $msg['schedule'] = $params->getSchedule();
            }

            if ($params->getRepeatingInterval()) {
                $msg['interval'] = $params->getRepeatingInterval();
            }
        }

        $_queue = &$this->data[$queue->getName()];

        $messageId = md5(Rand::getString(10) . count($_queue));

        $_queue[$messageId] = $msg;

        $this->embedMessageInfo($queue, $message, $messageId, $params);

        return $message;
    }

    /**
     * Get messages in the queue
     *
     * @param QueueInterface $queue
     * @param integer $maxMessages Maximum number of messages to return
     * @param ReceiveParametersInterface $params
     * @return MessageIterator
     */
    public function receiveMessages(
        QueueInterface $queue,
        $maxMessages = null,
        ReceiveParametersInterface $params = null
    ) {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }

        $timeout = $params ? $params->getVisibilityTimeout() : null;
        $filter = $params ? $params->getClassFilter() : null;
        $peek = $params ? $params->getPeekMode() : false;
        $microtime = (int)microtime(true);

        $data = [];
        if ($maxMessages > 0) {
            $count = 0;
            $temp = &$this->data[$queue->getName()];
            foreach ($temp as $messageId => &$msg) {
                if (null !== $filter && $msg['class'] != $filter) {
                    continue;
                }

                if (isset($msg['schedule']) && $microtime < $msg['schedule']) {
                    continue;
                }

                if ($msg['handle'] === null || ($msg['timeout'] !== null && $msg['timeout'] < $microtime)) {
                    if ($peek) {
                        $msg['handle'] = null;
                        $msg['timeout'] = null;
                    } else {
                        $msg['handle'] = md5(uniqid(rand(), true));
                        $msg['timeout'] = $timeout ? $microtime + $timeout : null;
                    }

                    $msg['metadata'][$queue->getOptions()->getMessageMetadatumKey()] = $this->buildMessageInfo(
                        $msg['handle'],
                        $messageId,
                        $queue,
                        [
                            SendParametersInterface::SCHEDULE => isset($msg['schedule']) ? $msg['schedule'] : null,
                            SendParametersInterface::REPEATING_INTERVAL => isset($msg['interval']) ?
                                $msg['interval'] :
                                null,
                        ]
                    );


                    $data[] = $msg;
                    ++$count;
                }

                if ($count >= $maxMessages) {
                    break;
                }
            }
        }

        $classname = $queue->getOptions()->getMessageSetClass();
        return new $classname($data, $queue);
    }

    /**
     * Delete a message from the queue
     *
     * Return true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  QueueInterface $queue
     * @param  MessageInterface $message
     * @return boolean
     * @throws Exception\QueueNotFoundException
     */
    public function deleteMessage(QueueInterface $queue, MessageInterface $message)
    {
        if (!$this->queueExists($queue->getName())) {
            throw new Exception\QueueNotFoundException('Queue does not exist:' . $queue->getName());
        }

        $info = $this->getMessageInfo($queue, $message);
        $messageId = $info['messageId'];

        // load the queue
        $queue = &$this->data[$queue->getName()];

        if (!array_key_exists($messageId, $queue)) {
            return false;
        }

        if (!empty($info['options'][SendParametersInterface::REPEATING_INTERVAL])) {
            $microtime = (int)microtime(true);
            $queue[$messageId]['schedule'] = $microtime + $info['options'][SendParametersInterface::REPEATING_INTERVAL];
            $queue[$messageId]['handle'] = null;
            $queue[$messageId]['timeout'] = null;
        } else {
            unset($queue[$messageId]);
        }

        return true;
    }

    /********************************************************************
     * Functions that are not part of the \Stakhanovist\Queue\Adapter\AdapterAbstract
     *********************************************************************/

    /**
     * serialize
     */
    public function __sleep()
    {
        return ['data'];
    }

    /*
     * These functions are debug helpers.
    */

    /**
     * returns underlying data array
     *
     * @return ArrayAdapter
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * sets the underlying data array
     *
     * @param $data array
     * @return ArrayAdapter
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}
