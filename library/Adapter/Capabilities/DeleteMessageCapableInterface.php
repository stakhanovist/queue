<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Adapter\Capabilities;

use Stakhanovist\Queue\Adapter\AdapterInterface;
use Stakhanovist\Queue\Exception;
use Stakhanovist\Queue\QueueInterface;
use Zend\Stdlib\MessageInterface;

/**
 * Interface DeleteMessageCapableInterface
 */
interface DeleteMessageCapableInterface extends AdapterInterface
{
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
    public function deleteMessage(QueueInterface $queue, MessageInterface $message);
}
