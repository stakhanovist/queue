<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueueTest;

use Zend\Config\Config;
use ZendQueue\Adapter;
use ZendQueue\Message\Message;
use ZendQueue\Queue;
use ZendQueue\QueueOptions;
use ZendQueue\Adapter\ArrayAdapter;
use ZendQueue\Message\MessageIterator;
use ZendQueue\Parameter\SendParameters;
use ZendQueue\Adapter\Null;
use ZendQueue\QueueEvent;

/*
 * The adapter test class provides a universal test class for all of the
 * abstract methods.
 *
 * All methods marked not supported are explictly checked for for throwing
 * an exception.
 */

/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage UnitTests
 * @group      Zend_Queue
 */
class QueueTest extends \PHPUnit_Framework_TestCase
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


    protected function setUp()
    {
        $this->name = 'queueTest';

        $this->options = new QueueOptions();

        $this->adapter = new ArrayAdapter();

        $this->queue = new Queue($this->name, $this->adapter, $this->options);

        $this->queue->ensureQueue();
    }

    protected function tearDown()
    {

    }

    public function test__construct()
    {
        //Test with two arguments
        $q = new Queue($this->name, $this->adapter);
        $this->assertInstanceOf('ZendQueue\QueueOptions', $q->getOptions());

        //Test empty queue name exception
        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');
        $q = new Queue('', $this->adapter);
    }


    public function testFactory()
    {
        $config = array(
                'name'     => 'A',
                'adapter'  => array( //Adapter as config
                    'adapter' => 'ArrayAdapter',
                    'options' => array('dummyOption' => 'dummyValue'),
                 ),
                'options' => array('messageClass' => 'Zend\Stdlib\Message'),
        );

        $q = Queue::factory($config);
        $this->assertInstanceOf('ZendQueue\Queue', $q);

        //Test traversable
        $config = new \ArrayObject($config);

        $q = Queue::factory($config);
        $this->assertInstanceOf('ZendQueue\Queue', $q);


        //Test invalid config type
        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');
        $q = Queue::factory('wrong config');
    }

    public function testFactoryMissingName()
    {
        $config = array(
            'name'     => 'A',
            'adapter'  => array( //Adapter as config
                'adapter' => 'ArrayAdapter',
                'options' => array('dummyOption' => 'dummyValue'),
            ),
            'options' => 'wrong options',
        );


        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');
        $q = Queue::factory($config);
    }

    public function testFactoryInvalidOptions()
    {
        $config = array(
            'adapter'  => array( //Adapter as config
                'adapter' => 'ArrayAdapter',
                'options' => array('dummyOption' => 'dummyValue'),
            ),
            'options' => array('messageClass' => 'Zend\Stdlib\Message'),
        );


        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');
        $q = Queue::factory($config);
    }

    public function testSetGetOptions()
    {
        $this->assertTrue($this->options instanceof QueueOptions);
        $this->assertEquals($this->options, $this->queue->getOptions());

        $options = new QueueOptions();

        $this->assertTrue($this->queue->setOptions($options) instanceof Queue);
        $this->assertEquals($options, $this->queue->getOptions());

        //test default options
        $q = new Queue($this->name, $this->adapter);
        $this->assertEquals($options, $q->getOptions());


    }

    public function testGetAdapter()
    {
        $this->assertTrue($this->queue->getAdapter() instanceof ArrayAdapter);
    }

    public function testGetName()
    {
        $this->assertEquals($this->name, $this->queue->getName());
    }

    public function testEnsureQueue()
    {
        $this->assertTrue($this->queue->ensureQueue());
        $this->assertTrue($this->adapter->queueExists($this->name));
    }

    public function testSampleBehavior()
    {
        // ------------------------------------ send()
        // parameter verification
        try {
            $this->queue->send(array());
            $this->fail('send() $mesage must be a string or an instance of \Zend\Stdlib\MessageInterface');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $message = 'Hello world';
        $this->assertInstanceOf('\Zend\Stdlib\MessageInterface', $this->queue->send($message));

        $newMessageObj = $this->queue->send(array(
            'content'   => $message,
            'metadata'  => array('foo' => 'bar')
        ));

        $this->assertInstanceOf('\Zend\Stdlib\MessageInterface', $newMessageObj);
        $this->assertEquals($message, $newMessageObj->getContent());
        $metadata = $newMessageObj->getMetadata();
        $this->assertArrayHasKey('__queue', $metadata);
        $this->assertArrayHasKey('foo', $metadata);
        $this->assertEquals('bar', $metadata['foo']);

        $message = new Message();
        $message->setContent('Hello world again');
        $this->assertEquals($message, $this->queue->send($message));


        // ------------------------------------ count()
        if ($this->queue->canCountMessages()) {
            $this->assertEquals($this->queue->count(), 3);
        }

        // ------------------------------------ receive()
        // parameter verification
        try {
            $this->queue->receive(array());
            $this->fail('receive() $maxMessages must be a integer or null');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // parameter verification
        try {
            $this->queue->receive(0);
            $this->fail('receive() $maxMessages must be a integer or null');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $messages = $this->queue->receive();
        $this->assertTrue($messages instanceof MessageIterator);

        // ------------------------------------ deleteMessage()
        if($this->queue->canDeleteMessage()) {
            foreach ($messages as $i => $message) {
                $this->assertTrue($message instanceof Message);
                $this->assertTrue($this->queue->deleteMessage($message));
            }
        }
    }

    /**
     * ArrayAdapter can't await, but emulation is active by default
     * @todo add EventManager case
     */
    public function testAwait()
    {
        if (!$this->queue->canAwait()) {
            $this->markTestSkipped('await() not supported');
        }



        $queueTest = $this;


        $this->queue->getEventManager()->attach(QueueEvent::EVENT_RECEIVE, function(QueueEvent $e) use ($queueTest) {

            $queueTest->assertInstanceOf('ZendQueue\Message\MessageIterator', $e->getMessages());
            $this->assertCount(1, $e->getMessages());
            $this->assertEquals('test', $e->getMessages()->current()->getContent());

        });

        $this->queue->getEventManager()->attach(QueueEvent::EVENT_IDLE, function(QueueEvent $e) use ($queueTest) {

//             $queueTest->assertInstanceOf('ZendQueue\Message\MessageIterator', $e->getMessages());
//             $this->assertCount(0, $e->getMessages());

            $e->stopPropagation(true);
        });

        //Ensure we have one message
        $this->queue->send('test');

        $this->assertInstanceOf('ZendQueue\Queue', $this->queue->await());

    }

    public function testAwaitUnsupported()
    {
        $q = clone $this->queue; //assume array adapter
        $q->getOptions()->setEnableAwaitEmulation(false);

        $this->setExpectedException('ZendQueue\Exception\UnsupportedMethodCallException');
        $q->await();

    }


    public function testCountUnsupported()
    {
        $q = new Queue('test', new Null());

        $this->assertFalse($q->canCountMessages());

        $this->setExpectedException('ZendQueue\Exception\UnsupportedMethodCallException');
        $q->count();
    }

    public function testDeleteMessageUnsupported()
    {
        $q = new Queue('test', new Null());

        $this->assertFalse($q->canDeleteMessage());

        $this->setExpectedException('ZendQueue\Exception\UnsupportedMethodCallException');
        $q->deleteMessage(new Message());
    }

}
