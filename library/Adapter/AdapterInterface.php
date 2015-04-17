<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Adapter;

use Traversable;
use Zend\Stdlib\MessageInterface;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\QueueInterface;
use Stakhanovist\Queue\Parameter\SendParametersInterface;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;

/**
 * Interface for common queue operations
 */
interface AdapterInterface
{
    /**
     * Constructor.
     *
     * $options is an array of key/value pairs or an instance of Traversable
     * containing configuration options.
     *
     * @param  array|Traversable $options An array having configuration data
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __construct($options = array());


    /**
     * Set options
     *
     * @param array|Traversable $options
     * @return AdapterInterface Fluent interface
     */
    public function setOptions($options);

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();


    /**
     * List avaliable params for sendMessage()
     *
     * @return array
     */
    public function getAvailableSendParams();

    /**
     * List avaliable params for receiveMessages()
     *
     * @return array
     */
    public function getAvailableReceiveParams();


    /**
     * Ensure connection
     *
     * @return bool
     */
    public function connect();


    /********************************************************************
     * Queue management functions
     *********************************************************************/

    /**
     * Returns the ID of the queue
     *
     * @param string $name Queue name
     * @return mixed
     * @throws Exception\QueueNotFoundException
     */
    public function getQueueId($name);

    /**
     * Check if a queue exists
     *
     * @param  string $name Queue name
     * @return boolean
     */
    public function queueExists($name);

    /**
     * Create a new queue
     *
     * @param  string $name Queue name
     * @return boolean
     */
    public function createQueue($name);

    /**
     * Delete a queue and all of its messages
     *
     * Return false if the queue is not found, true if the queue exists.
     *
     * @param  string $name Queue name
     * @return boolean
     */
    public function deleteQueue($name);


    /********************************************************************
     * Message management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  QueueInterface $queue
     * @param  MessageInterface $message Message to send to the active queue
     * @param  SendParametersInterface $params
     * @return MessageInterface
     * @throws Exception\QueueNotFoundException
     * @throws Exception\RuntimeException
     */
    public function sendMessage(QueueInterface $queue, MessageInterface $message, SendParametersInterface $params = null);

    /**
     * Get messages from the queue
     *
     * @param  QueueInterface $queue
     * @param  integer|null $maxMessages Maximum number of messages to return
     * @param  ReceiveParametersInterface $params
     * @return MessageIterator
     * @throws Exception\QueueNotFoundException
     * @throws Exception\RuntimeException
     */
    public function receiveMessages(QueueInterface $queue, $maxMessages = null, ReceiveParametersInterface $params = null);

    /**
     * Get message info
     *
     * Only received messages have embedded infos.
     *
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @return array
     */
    public function getMessageInfo(QueueInterface $queue, MessageInterface $message);

}
