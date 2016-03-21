<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Adapter;

use Stakhanovist\Queue\Exception;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;
use Stakhanovist\Queue\Parameter\SendParametersInterface;
use Stakhanovist\Queue\QueueInterface;
use Zend\Stdlib\MessageInterface;

/**
 * Class NullAdapter
 *
 * Class for testing. No supported functions. Also used to disable a queue.
 */
class NullAdapter extends AbstractAdapter
{
    /**
     * Ensure connection
     *
     * @return bool
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
     */
    public function getQueueId($name)
    {
        return null;
    }

    /**
     * Check if a queue exists
     *
     * @param  string $name Queue name
     * @return bool
     */
    public function queueExists($name)
    {
        return false;
    }

    /**
     * Create a new queue
     *
     * @param  string $name Queue name
     * @return bool|void
     * @throws Exception\UnsupportedMethodCallException - queue disabled
     */
    public function createQueue($name)
    {
        throw new Exception\UnsupportedMethodCallException(
            __FUNCTION__ . '() is not supported by ' . get_called_class()
        );
    }

    /**
     * Delete a queue and all of its messages
     *
     * Return false if the queue is not found, true if the queue exists.
     *
     * @param  string $name Queue name
     * @return bool|void
     * @throws Exception\UnsupportedMethodCallException - queue disabled
     */
    public function deleteQueue($name)
    {
        throw new Exception\UnsupportedMethodCallException(
            __FUNCTION__ . '() is not supported by ' . get_called_class()
        );
    }

    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  QueueInterface $queue
     * @param  MessageInterface $message Message
     * @param  SendParametersInterface $params
     * @return void|MessageInterface
     * @throws Exception\UnsupportedMethodCallException - queue disabled
     */
    public function sendMessage(
        QueueInterface $queue,
        MessageInterface $message,
        SendParametersInterface $params = null
    ) {
        throw new Exception\UnsupportedMethodCallException(
            __FUNCTION__ . '() is not supported by ' . get_called_class()
        );
    }

    /**
     * Get messages from the queue
     *
     * @param  QueueInterface $queue
     * @param  integer|null $maxMessages Maximum number of messages to return
     * @param  ReceiveParametersInterface $params
     * @return \Stakhanovist\Queue\Message\MessageIterator|void
     * @throws Exception\UnsupportedMethodCallException - queue disabled
     */
    public function receiveMessages(
        QueueInterface $queue,
        $maxMessages = null,
        ReceiveParametersInterface $params = null
    ) {
        throw new Exception\UnsupportedMethodCallException(
            __FUNCTION__ . '() is not supported by ' . get_called_class()
        );
    }
}
