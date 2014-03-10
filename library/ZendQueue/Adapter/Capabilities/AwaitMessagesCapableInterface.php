<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueue\Adapter\Capabilities;
use ZendQueue\Adapter\AdapterInterface;
use ZendQueue\Parameter\ReceiveParameters;
use ZendQueue\QueueInterface as Queue;
use ZendQueue\Message\MessageIterator;

interface AwaitMessagesCapableInterface extends AdapterInterface
{
     /**
     * Await for a message in the queue and receive it
     * If no message arrives until timeout, an empty MessageSet will be returned.
     *
     * @param  Queue $queue
     * @param  callable $callback
     * @param  ReceiveParameters $params
     * @return MessageIterator
     * @throws Exception\RuntimeException
     */
    public function awaitMessages(Queue $queue, $callback, ReceiveParameters $params = null);
}
