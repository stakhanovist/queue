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
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Parameter\ReceiveParametersInterface;
use Stakhanovist\Queue\QueueInterface;

interface AwaitMessagesCapableInterface extends AdapterInterface
{
     /**
     * Await for a message in the queue and receive it
     * If no message arrives until timeout, an empty MessageSet will be returned.
     *
     * @param  QueueInterface $queue
     * @param  callable $callback
     * @param  ReceiveParametersInterface $params
     * @return MessageIterator
     * @throws Exception\RuntimeException
     */
    public function awaitMessages(QueueInterface $queue, $callback, ReceiveParametersInterface $params = null);
}
