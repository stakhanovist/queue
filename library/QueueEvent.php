<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue;

use Zend\EventManager\Event;
use Stakhanovist\Queue\Message\MessageIterator;

class QueueEvent extends Event
{

    /**#@+
     * Queue events triggered by eventmanager
     */
    const EVENT_RECEIVE     = 'receive';
    const EVENT_IDLE        = 'idle';
    /**#@-*/

    /**
     * @var MessageIterator
     */
    protected $messages;

    /**
     * @var bool
     */
    protected $stopAwait = false;

    /**
     * @param MessageIterator $messages
     * @return QueueEvent
     */
    public function setMessages(MessageIterator $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return MessageIterator
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Stop queue await
     *
     * @param  bool $flag
     * @return void
     */
    public function stopAwait($flag = true)
    {
        $this->stopAwait = (bool) $flag;
    }

    /**
     * Is await stopped?
     *
     * @return bool
     */
    public function awaitIsStopped()
    {
        return $this->stopAwait;
    }






}
