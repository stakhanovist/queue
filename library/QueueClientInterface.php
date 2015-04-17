<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue;

use Countable;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\MessageInterface;
use Stakhanovist\Queue\Exception;
use Stakhanovist\Queue\Adapter\AdapterInterface;
use Stakhanovist\Queue\Adapter\Capabilities\AwaitMessagesCapableInterface;
use Stakhanovist\Queue\Adapter\Capabilities\CountMessagesCapableInterface;
use Stakhanovist\Queue\Adapter\Capabilities\DeleteMessageCapableInterface;
use Stakhanovist\Queue\Parameter\SendParametersInterface;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Stakhanovist\Queue\Adapter\AdapterFactory;
use Zend\EventManager\EventManager;
use Stakhanovist\Queue\Message\MessageIterator;
use Zend\EventManager\EventManagerAwareInterface;

/**
 *
 */
interface QueueClientInterface extends QueueInterface, Countable
{
    /**
     * Set options
     *
     * @param  QueueOptionsInterface $options
     * @return $this
     */
    public function setOptions(QueueOptionsInterface $options);

    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @param  SendParametersInterface $params
     * @return MessageInterface
     * @throws Exception\ExceptionInterface
     */
    public function send($message, SendParametersInterface $params = null);

    /**
     * Return the first element in the queue
     *
     * @param  integer $maxMessages
     * @param  ReceiveParametersInterface $params
     * @return Message\MessageIterator
     * @throws Exception\InvalidArgumentException
     */
    public function receive($maxMessages = 1, ReceiveParametersInterface $params = null);

    /**
     * Await messages
     *
     * @param  ReceiveParametersInterface $params
     * @return Queue
     * @throws Exception\InvalidArgumentException
     */
    public function await(ReceiveParametersInterface $params = null);

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
    public function delete(MessageInterface $message);

    /**
     * @param bool $name
     */
    public function isSendParamSupported($name);

    /**
     * @param bool $name
     */
    public function isReceiveParamSupported($name);

    /**
     * Can queue wait for messages?
     *
     * Return true if the adapter is await-capable or enableAwaitEmulation is active.
     *
     * @return bool
     */
    public function canAwait();

    /**
     * Can queue delete message?
     *
     * Return true if the adapter is capable to delete messages.
     *
     * @return bool
     */
    public function canDeleteMessage();

    /**
     * Can count in queue messages?
     *
     * Return true if the adapter can count messages.
     *
     * @return bool
     */
    public function canCountMessages();

}
