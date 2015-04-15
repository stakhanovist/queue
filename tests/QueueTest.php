<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest;

use Zend\Config\Config;
use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Message\Message;
use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\QueueOptions;
use Stakhanovist\Queue\Adapter\ArrayAdapter;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Parameter\SendParameters;
use Stakhanovist\Queue\Adapter\Null;
use Stakhanovist\Queue\QueueEvent;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Stakhanovist\Queue\Parameter\ReceiveParameters;

/*
 * The adapter test class provides a universal test class for all of the
 * abstract methods.
 *
 * All methods marked not supported are explictly checked for for throwing
 * an exception.
 */

/**
 *
 * @group      Stakhanovist_Queue
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
        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $q->getOptions());

        //Test empty queue name exception
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
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
        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $q);

        //Test traversable
        $config = new \ArrayObject($config);

        $q = Queue::factory($config);
        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $q);


        //Test invalid config type
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
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


        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
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


        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
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

        $receiveHandler = $this->queue->getEventManager()->attach(QueueEvent::EVENT_RECEIVE, function (QueueEvent $e) use ($queueTest, &$eventReceiveTriggered) {

            $eventReceiveTriggered = true;
            $queueTest->assertInstanceOf('Stakhanovist\Queue\Message\MessageIterator', $e->getMessages());
            $queueTest->assertCount(1, $e->getMessages());
            $queueTest->assertEquals('test', $e->getMessages()->current()->getContent());

        });


        $idleHandler = $this->queue->getEventManager()->attach(QueueEvent::EVENT_IDLE, function (QueueEvent $e) use ($queueTest, &$eventIdleTriggered) {

            $eventIdleTriggered = true;
            $queueTest->assertInstanceOf('Stakhanovist\Queue\Message\MessageIterator', $e->getMessages());

            $e->stopAwait(true);
        });

        //Ensure we have one message
        $this->queue->send('test');

        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $this->queue->await());
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

        $receiveHandler= $this->queue->getEventManager()->attach(QueueEvent::EVENT_RECEIVE, function (QueueEvent $e) use ($queueTest, &$eventReceiveTriggered) {
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

        $idleHandler= $this->queue->getEventManager()->attach(QueueEvent::EVENT_IDLE, function (QueueEvent $e) use ($queueTest, &$triggerCount) {
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

        $this->setExpectedException('Stakhanovist\Queue\Exception\UnsupportedMethodCallException');
        $q->await();

    }

    public function testAwaitWithParamsAndCapableAdapter()
    {
        $mockAdapter = $this->getMock('Stakhanovist\Queue\Adapter\Capabilities\AwaitMessagesCapableInterface');

        $receiveParams = new ReceiveParameters();

        $q = new Queue('test', $mockAdapter);

        $mockAdapter->expects($this->any())->method('awaitMessages')->with(
            $this->equalTo($q), $this->equalTo(function () {}), $this->equalTo($receiveParams)
        );

        $this->assertSame($q, $q->await($receiveParams));
    }


    public function testCountUnsupported()
    {
        $q = new Queue('test', new Null());

        $this->assertFalse($q->canCountMessages());

        $this->setExpectedException('Stakhanovist\Queue\Exception\UnsupportedMethodCallException');
        $q->count();
    }

    public function testDeleteMessageUnsupported()
    {
        $q = new Queue('test', new Null());

        $this->assertFalse($q->canDeleteMessage());

        $this->setExpectedException('Stakhanovist\Queue\Exception\UnsupportedMethodCallException');
        $q->delete(new Message());
    }

    public function testScheduleMessage()
    {
        $time = time() + 3600;
        $repeting = 60;
        $message = new Message();

        $expectedParams = new SendParameters();
        $expectedParams->setSchedule($time);
        $expectedParams->setRepeatingInterval($repeting);

        $mockAdapter = $this->getMock('Stakhanovist\Queue\Adapter\AdapterInterface');
        $mockAdapter->expects($this->any())->method('getAvailableSendParams')->will($this->returnValue(array(
            SendParameters::SCHEDULE,
            SendParameters::REPEATING_INTERVAL
        )));

        $q = new Queue('test', $mockAdapter);

        $mockAdapter->expects($this->any())->method('sendMessage')->with(
            $this->equalTo($q), $this->equalTo($message), $this->equalTo($expectedParams)
        )->will($this->returnValue($message));

        $this->assertTrue($q->isSendParamSupported(SendParameters::SCHEDULE));
        $this->assertTrue($q->isSendParamSupported(SendParameters::REPEATING_INTERVAL));

        $this->assertSame($message, $q->schedule($message, $time, $repeting));
    }

    public function testScheduleMessageUnsupported()
    {
        $q = new Queue('test', new Null());

        $this->assertFalse($q->isSendParamSupported(SendParameters::SCHEDULE));

        $this->setExpectedException('Stakhanovist\Queue\Exception\UnsupportedMethodCallException');
        $q->schedule(new Message());
    }

    public function testScheduleMessageRepeatingIntervalUnsupported()
    {
        $mockAdapter = $this->getMock('Stakhanovist\Queue\Adapter\AdapterInterface');
        $mockAdapter->expects($this->any())->method('getAvailableSendParams')->will($this->returnValue(array(
            SendParameters::SCHEDULE
        )));
        $mockAdapter->expects($this->any())->method('sendMessage');


        $q = new Queue('test', $mockAdapter);

        $this->assertTrue($q->isSendParamSupported(SendParameters::SCHEDULE));
        $this->assertFalse($q->isSendParamSupported(SendParameters::REPEATING_INTERVAL));

        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $q->schedule(new Message(), 1, 1);
    }

    public function testUnscheduleMessage()
    {
        $message = new Message();

        $providedOptions = array(
            SendParameters::SCHEDULE            => time() + 60,
            SendParameters::REPEATING_INTERVAL  => 3600,
        );

        $mockAdapter = $this->getMock('Stakhanovist\Queue\Adapter\Capabilities\DeleteMessageCapableInterface');
        $mockAdapter->expects($this->any())->method('getAvailableSendParams')->will($this->returnValue(array(
            SendParameters::SCHEDULE,
            SendParameters::REPEATING_INTERVAL
        )));

        $q = new Queue('test', $mockAdapter);
        $message->setMetadata($q->getOptions()->getMessageMetadatumKey(), array('options' => $providedOptions));


        $mockAdapter->expects($this->any())->method('getMessageInfo')->with(
            $this->equalTo($q), $this->equalTo($message)
        )->will($this->returnValue(array('options' => $providedOptions)));


        $mockAdapter->expects($this->any())->method('deleteMessage')->with(
            $this->equalTo($q), $this->equalTo($message)
        )->will($this->returnValue(true));


        $this->assertTrue($q->isSendParamSupported(SendParameters::SCHEDULE));
        $this->assertTrue($q->isSendParamSupported(SendParameters::REPEATING_INTERVAL));

        $this->assertTrue($q->unschedule($message));

        $messageInfo = $message->getMetadata($q->getOptions()->getMessageMetadatumKey());
        $this->assertArrayHasKey('options', $messageInfo);
        $this->assertArrayNotHasKey(SendParameters::SCHEDULE, $messageInfo['options']);
        $this->assertArrayNotHasKey(SendParameters::REPEATING_INTERVAL, $messageInfo['options']);
    }

    public function testUnscheduleMessageUnsupported()
    {
        $q = new Queue('test', new Null());

        $this->assertFalse($q->isSendParamSupported(SendParameters::SCHEDULE));

        $this->setExpectedException('Stakhanovist\Queue\Exception\UnsupportedMethodCallException');
        $q->unschedule(new Message());
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
        $adapterMock = $this->getMock('Stakhanovist\Queue\Adapter\AdapterInterface');
        $adapterMock->expects($this->any())->method("getAvailableSendParams")->will($this->returnValue(array('foo')));
        $q = new Queue('test', $adapterMock);
        $this->isTrue($q->isSendParamSupported('foo'));
        $this->isFalse($q->isSendParamSupported('bar'));
    }

    public function testIsReceiveParamSupported()
    {
        $adapterMock = $this->getMock('Stakhanovist\Queue\Adapter\AdapterInterface');
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
        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $this->queue->setEventManager($newEventManager));

        $this->assertSame($newEventManager, $this->queue->getEventManager());

        //Restore original manager
        $this->queue->setEventManager($defaultEventManager);

    }

    public function testGetSetEvent()
    {
        $defaultEvent = $this->queue->getEvent();
        $this->assertInstanceOf('Stakhanovist\Queue\QueueEvent', $defaultEvent);

        $newEvent = new Event();
        $newEvent->setParam('foo', 'bar');

        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $this->queue->setEvent($newEvent));

        //Test recast
        $this->assertInstanceOf('Stakhanovist\Queue\QueueEvent', $this->queue->getEvent());
        $this->assertSame('bar', $this->queue->getEvent()->getParam('foo'));

        //Restore original event
        $this->queue->setEvent($defaultEvent);

    }

    public function testUsageExample()
    {
        // Create an array queue adapter
        $adapter = new ArrayAdapter();


        // Create a queue object
        $queue = new Queue('queue1', $adapter);

        // Ensure queue1 exists in the backend
        $queue->ensureQueue();


        // Create a new queue object
        $queue2 = new Queue('queue2', $adapter);

        // Ensure queue2 exists in the backend
        $queue2->ensureQueue();


        // Get list of queues
        foreach ($adapter->listQueues() as $name) {
            $this->assertStringStartsWith('queue', $name); //echo $name, "\n";
        }


        // Send a message to queue1
        $queue->send('My Test Message');


        // Get number of messages in a queue1 (supports Countable interface from SPL)
        $this->assertCount(1, $queue);//echo count($queue);

        // Get up to 5 messages from a queue1
        $messages = $queue->receive(5);

        foreach ($messages as $i => $message) {
            $this->assertSame('My Test Message', $message->getContent()); //echo $message->getContent(), "\n";

            // We have processed the message; now we remove it from the queue.
            $queue->delete($message);
        }


        // Delete a queue we created and all of it's messages
        $queue->deleteQueue();

    }


}
