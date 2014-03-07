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
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;

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
            'name' => 'A',
            'adapter' => array( //Adapter as config
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
            'name' => 'A',
            'adapter' => array( //Adapter as config
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
            'adapter' => array( //Adapter as config
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
            'content' => $message,
            'metadata' => array('foo' => 'bar')
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
        if ($this->queue->canDeleteMessage()) {
            foreach ($messages as $i => $message) {
                $this->assertTrue($message instanceof Message);
                $this->assertTrue($this->queue->delete($message));
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
        $eventReceiveTriggered = false;
        $eventIdleTriggered = false;

        $receiveHandler = $this->queue->getEventManager()->attach(QueueEvent::EVENT_RECEIVE, function(QueueEvent $e) use ($queueTest, &$eventReceiveTriggered) {

            $eventReceiveTriggered = true;
            $queueTest->assertInstanceOf('ZendQueue\Message\MessageIterator', $e->getMessages());
            $queueTest->assertCount(1, $e->getMessages());
            $queueTest->assertEquals('test', $e->getMessages()->current()->getContent());

        });


        $idleHandler = $this->queue->getEventManager()->attach(QueueEvent::EVENT_IDLE, function(QueueEvent $e) use ($queueTest, &$eventIdleTriggered) {

            $eventIdleTriggered = true;
            $queueTest->assertInstanceOf('ZendQueue\Message\MessageIterator', $e->getMessages());

            $e->stopAwait(true);
        });

        //Ensure we have one message
        $this->queue->send('test');

        $this->assertInstanceOf('ZendQueue\Queue', $this->queue->await());
        $this->assertTrue($eventReceiveTriggered, 'QueueEvent::EVENT_RECEIVE has been not triggered');
        $this->assertTrue($eventIdleTriggered, 'QueueEvent::EVENT_IDLE has been not triggered');

        //Cleanup
        $this->queue->getEventManager()->detach($receiveHandler);
        $this->queue->getEventManager()->detach($idleHandler);
    }


    public function testStopAwaitOnReceive()
    {
        if (!$this->queue->canAwait()) {
            $this->markTestSkipped('await() not supported');
        }

        $queueTest = $this;
        $eventReceiveTriggered = false;

        $receiveHandler= $this->queue->getEventManager()->attach(QueueEvent::EVENT_RECEIVE, function(QueueEvent $e) use ($queueTest, &$eventReceiveTriggered) {
            $eventReceiveTriggered = true;
            $e->stopAwait(true);
        });

        //Ensure we have one message
        $this->queue->send('test');

       $this->queue->await();
       $this->assertTrue($eventReceiveTriggered, 'QueueEvent::EVENT_RECEIVE has been not triggered');

       //Cleanup
       $this->queue->getEventManager()->detach($receiveHandler);

    }

    public function testAwaitEmptyQueue()
    {
        if (!$this->queue->canAwait()) {
            $this->markTestSkipped('await() not supported');
        }

        $queueTest = $this;
        $triggerCount = 0;

        $idleHandler= $this->queue->getEventManager()->attach(QueueEvent::EVENT_IDLE, function(QueueEvent $e) use ($queueTest, &$triggerCount) {
            $triggerCount++;
            if ($triggerCount == 2) {
                $e->stopAwait(true);
            }
        });

        $this->queue->await();
        $this->assertEquals(2, $triggerCount, 'QueueEvent::EVENT_IDLE has been not triggered 2 times');

        //Cleanup
        $this->queue->getEventManager()->detach($idleHandler);

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
        $q->delete(new Message());
    }

    public function testScheduleMessage()
    {

    }

    public function testDebugInfo()
    {
        $q = new Queue('test', new ArrayAdapter());
        $this->assertInternalType('array', $q->debugInfo());
    }

    public function testIsAwaitEmulation()
    {
        $q = new Queue('test', new ArrayAdapter());
        $this->isTrue($q->isAwaitEmulation());
    }

    public function testIsSendParamSupported()
    {
        $adapterMock = $this->getMock('ZendQueue\Adapter\AdapterInterface');
        $adapterMock->expects($this->any())->method("getAvailableSendParams")->will($this->returnValue(array('foo')));
        $q = new Queue('test', $adapterMock);
        $this->isTrue($q->isSendParamSupported('foo'));
        $this->isFalse($q->isSendParamSupported('bar'));
    }

    public function testIsReceiveParamSupported()
    {
        $adapterMock = $this->getMock('ZendQueue\Adapter\AdapterInterface');
        $adapterMock->expects($this->any())->method("getAvailableReceiveParams")->will($this->returnValue(array('foo')));
        $q = new Queue('test', $adapterMock);
        $this->isTrue($q->isReceiveParamSupported('foo'));
        $this->isFalse($q->isReceiveParamSupported('bar'));
    }

    public function testQueueIsEventManagerAware()
    {
        $this->assertInstanceOf('Zend\EventManager\EventManagerAwareInterface', $this->queue);

        $defaultEventManager = $this->queue->getEventManager();
        $this->assertInstanceOf('Zend\EventManager\EventManagerInterface', $defaultEventManager);

        $newEventManager = new EventManager();
        $this->assertInstanceOf('ZendQueue\Queue', $this->queue->setEventManager($newEventManager));

        $this->assertSame($newEventManager, $this->queue->getEventManager());

        //Restore original manager
        $this->queue->setEventManager($defaultEventManager);

    }

    public function testGetSetEvent()
    {
        $defaultEvent = $this->queue->getEvent();
        $this->assertInstanceOf('ZendQueue\QueueEvent', $defaultEvent);

        $newEvent = new Event();
        $newEvent->setParam('foo', 'bar');

        $this->assertInstanceOf('ZendQueue\Queue', $this->queue->setEvent($newEvent));

        //Test recast
        $this->assertInstanceOf('ZendQueue\QueueEvent', $this->queue->getEvent());
        $this->assertSame('bar', $this->queue->getEvent()->getParam('foo'));

        //Restore original event
        $this->queue->setEvent($defaultEvent);

    }


}
