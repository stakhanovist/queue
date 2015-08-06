<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Message;

use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\Message\Message;
use Stakhanovist\Queue\QueueOptions;
use Stakhanovist\Queue\Adapter\ArrayAdapter;

/*
 * The adapter test class provides a universal test class for all of the
* abstract methods.
*
* All methods marked not supported are explictly checked for for throwing
* an exception.
*/
/**
 * Stakhanovist\Queue\Message is just a placeholder for Zend\Stdlib\Message
 * so we don't need ro repeat all tests.
 *
 * Just tests for seralization and check if it's the default message class
 *
 *
 * @group      Stakhanovist_Queue
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayAdapter
     */
    protected $adapter;

    /**
     * @var QueueOptions
     */
    protected $options;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Message
     */
    protected $message;


    protected function setUp()
    {
        $this->name = 'queueTest';

        $this->options = new QueueOptions();

        $this->adapter = new ArrayAdapter();

        $this->queue = new Queue($this->name, $this->adapter, $this->options);

        $this->queue->ensureQueue();

        $this->message = new Message();
    }

    protected function tearDown()
    {
    }


    public function testSerialization()
    {
        $message = serialize($this->message);
        $woken = unserialize($message);
        $this->assertEquals($this->message->getContent(), $woken->getContent());
        $this->assertEquals($this->message->getMetadata(), $woken->getMetadata());
    }

    public function testDefaultMessageClass()
    {
        $this->queue->send('testMessage');
        $messages = $this->queue->receive();

        $this->assertInstanceOf('\Stakhanovist\Queue\Message\Message', $messages->current());
    }
}
