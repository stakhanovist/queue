<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Message;

use Stakhanovist\Queue\Adapter\ArrayAdapter;
use Stakhanovist\Queue\Message\Message;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\QueueOptions;
use Zend\Stdlib\Message as ZendMessage;

/**
 * Class MessageIteratorTest
 *
 * @group message
 */
class MessageIteratorTest extends \PHPUnit_Framework_TestCase
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
     * @var MessageIterator
     */
    protected $messages;

    protected $metadata;

    protected $message_count;

    protected function setUp()
    {
        $this->name = 'queueTest';

        $this->options = new QueueOptions;

        $this->adapter = new ArrayAdapter;

        $this->queue = new Queue($this->name, $this->adapter, $this->options);

        // construct messages
        $this->message_count = 5;
        $data = [];
        $this->metadata = [
            'one' => 1,
            'two' => 2,
        ];
        for ($i = 0; $i < $this->message_count; $i++) {
            $data[] = [
                'class' => Message::class,
                'metadata' => $this->metadata,
                'content' => 'Hello world',
            ];
        }

        $classname = $this->queue->getOptions()->getMessageSetClass();
        $this->messages = new $classname($data, $this->queue);
    }


    public function testSetup()
    {
        $classname = $this->queue->getOptions()->getMessageSetClass();
        $this->assertTrue($this->queue instanceof Queue);
        $this->assertTrue($this->options instanceof QueueOptions);
        $this->assertInstanceOf($classname, $this->messages);

        foreach ($this->messages as $i => $message) {
            $this->assertTrue($message instanceof Message);
            $this->assertEquals('Hello world', $message->getContent());
            $this->assertEquals($this->metadata, $message->getMetadata());
        }
    }

    public function testConstruct()
    {
        $message = new Message;
        $message->setContent('A message');

        $stdMessage = new ZendMessage;
        $stdMessage->setContent('A stdlib message');

        //Test array of Message, without queue in constructor
        $messages = new MessageIterator([$message, $stdMessage]);

        $this->assertEquals($message, $messages->current());
        $messages->next();
        $this->assertEquals($stdMessage, $messages->current());
        $this->assertNull($messages->getQueue());
        $this->assertNull($messages->getQueueClass());

        //Test array of array, without queue in constructor
        $messages = new MessageIterator([['content' => 'a message']]);
        $this->assertEquals('a message', $messages->current()->getContent());
        $this->assertInstanceOf($this->queue->getOptions()->getMessageClass(), $messages->current());

        //Test with queue in constructor
        $messages = new MessageIterator([$message, $stdMessage], $this->queue);
        $this->assertTrue($this->queue === $messages->getQueue());
        $this->assertEquals(get_class($this->queue), $this->messages->getQueueClass());
    }

    public function testCount()
    {
        $this->assertEquals($this->message_count, count($this->messages));
    }

    public function testMagic()
    {
        $this->assertTrue(is_array($this->messages->__sleep()));

        $messages = serialize($this->messages);
        $woken = unserialize($messages);
        $this->assertEquals($this->messages->current()->getContent(), $woken->current()->getContent());
        $this->assertNull($woken->getQueue());
    }

    public function testGetSetQueue()
    {
        $queue = $this->messages->getQueue();
        $this->assertTrue($queue instanceof Queue);

        $this->assertTrue($this->messages->setQueue($queue) instanceof MessageIterator);

        $this->assertTrue($this->messages->getQueue() === $queue);
    }

    public function testGetQueueClass()
    {
        $this->assertEquals(get_class($this->queue), $this->messages->getQueueClass());
    }

    public function testIterator()
    {
        foreach ($this->messages as $i => $message) {
            $this->assertEquals('Hello world', $message->getContent());
            $this->assertEquals($this->metadata, $message->getMetadata());
        }
    }
}
