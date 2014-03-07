<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueue;

use Zend\EventManager\Event;
use ZendQueue\Message\MessageIterator;

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