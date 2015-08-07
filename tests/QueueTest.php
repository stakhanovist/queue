<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest;

use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Adapter\ArrayAdapter;
use Stakhanovist\Queue\Adapter\NullAdapter;
use Stakhanovist\Queue\Exception\InvalidArgumentException;
use Stakhanovist\Queue\Exception\UnsupportedMethodCallException;
use Stakhanovist\Queue\Message\Message;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Parameter\ReceiveParameters;
use Stakhanovist\Queue\Parameter\SendParameters;
use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\QueueClientInterface;
use Stakhanovist\Queue\QueueEvent;
use Stakhanovist\Queue\QueueInterface;
use Stakhanovist\Queue\QueueOptions;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\Message as ZendMessage;
use Zend\Stdlib\MessageInterface;

/**
 * Class QueueTest
 *
 * @group queue
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

    public function testConstruct()
    {
        // Test with two arguments
        $q = new Queue($this->name, $this->adapter);
        $this->assertInstanceOf(QueueOptions::class, $q->getOptions());

        // Test with three arguments
        $options = new QueueOptions();
        $q = new Queue($this->name, $this->adapter, $options);
        $this->assertSame($options, $q->getOptions());

        // Test implents interfaces
        $this->assertInstanceOf(QueueInterface::class, $q);
        $this->assertInstanceOf(QueueClientInterface::class, $q);
        $this->assertInstanceOf(\Countable::class, $q);
        $this->assertInstanceOf(EventManagerAwareInterface::class, $q);

        // Test empty queue name exception
        $this->setExpectedException(InvalidArgumentException::class);
        new Queue('', $this->adapter);
    }


    public function testFactory()
    {
        $config = [
            'name' => 'A',
            'adapter' => [ //Adapter as config
                'adapter' => 'array',
                'options' => ['dummyOption' => 'dummyValue'],
            ],
            'options' => ['messageClass' => ZendMessage::class],
        ];

        $q = Queue::factory($config);
        $this->assertInstanceOf(Queue::class, $q);

        // Test traversable
        $config = new \ArrayObject($config);

        $q = Queue::factory($config);
        $this->assertInstanceOf(Queue::class, $q);


        // Test invalid config type
        $this->setExpectedException(InvalidArgumentException::class);
        Queue::factory('wrong config');
    }

    public function testFactoryMissingName()
    {
        $config = [
            'name' => 'A',
            'adapter' => [ //Adapter as config
                'adapter' => 'array',
                'options' => ['dummyOption' => 'dummyValue'],
            ],
            'options' => 'wrong options',
        ];


        $this->setExpectedException(InvalidArgumentException::class);
        Queue::factory($config);
    }

    public function testFactoryInvalidOptions()
    {
        $config = [
            'adapter' => [ //Adapter as config
                'adapter' => 'array',
                'options' => ['dummyOption' => 'dummyValue'],
            ],
            'options' => ['messageClass' => ZendMessage::class],
        ];

        $this->setExpectedException(InvalidArgumentException::class);
        Queue::factory($config);
    }

    public function testSetGetOptions()
    {
        $this->assertTrue($this->options instanceof QueueOptions);
        $this->assertEquals($this->options, $this->queue->getOptions());

        $options = new QueueOptions;

        $this->assertTrue($this->queue->setOptions($options) instanceof Queue);
        $this->assertEquals($options, $this->queue->getOptions());

        // Test default options
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
        // send()
        // Parameter verification
        try {
            $this->queue->send([]);
            $this->fail('send() $mesage must be a string or an instance of \Zend\Stdlib\MessageInterface');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $message = 'Hello world';
        $this->assertInstanceOf(MessageInterface::class, $this->queue->send($message));

        $newMessageObj = $this->queue->send(
            [
                'content' => $message,
                'metadata' => ['foo' => 'bar']
            ]
        );

        $this->assertInstanceOf(MessageInterface::class, $newMessageObj);
        $this->assertEquals($message, $newMessageObj->getContent());
        $metadata = $newMessageObj->getMetadata();
        $this->assertArrayHasKey('__queue', $metadata);
        $this->assertArrayHasKey('foo', $metadata);
        $this->assertEquals('bar', $metadata['foo']);

        $message = new Message();
        $message->setContent('Hello world again');
        $this->assertEquals($message, $this->queue->send($message));


        // count()
        if ($this->queue->canCountMessages()) {
            $this->assertEquals($this->queue->count(), 3);
        }

        // receive()
        // Parameter verification
        try {
            $this->queue->receive([]);
            $this->fail('Method receive() $maxMessages must be a integer or null');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // parameter verification
        try {
            $this->queue->receive(0);
            $this->fail('Method receive() $maxMessages must be a integer or null');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $messages = $this->queue->receive();
        $this->assertTrue($messages instanceof MessageIterator);

        // deleteMessage()
        if ($this->queue->canDeleteMessage()) {
            foreach ($messages as $i => $message) {
                $this->assertTrue($message instanceof Message);
                $this->assertTrue($this->queue->delete($message));
            }
        }
    }

    /**
     * ArrayAdapter can't await, but emulation is active by default
     *
     * @todo add EventManager case
     */
    public function testAwait()
    {
        if (!$this->queue->canAwait()) {
            $this->markTestSkipped('Method await() not supported');
        }

        $queueTest = $this;
        $eventReceiveTriggered = false;
        $eventIdleTriggered = false;

        $receiveHandler = $this->queue->getEventManager()->attach(
            QueueEvent::EVENT_RECEIVE,
            function (QueueEvent $e) use ($queueTest, &$eventReceiveTriggered) {

                $eventReceiveTriggered = true;
                $queueTest->assertInstanceOf(MessageIterator::class, $e->getMessages());
                $queueTest->assertCount(1, $e->getMessages());
                $queueTest->assertEquals('test', $e->getMessages()->current()->getContent());

            }
        );


        $idleHandler = $this->queue->getEventManager()->attach(
            QueueEvent::EVENT_IDLE,
            function (QueueEvent $e) use ($queueTest, &$eventIdleTriggered) {

                $eventIdleTriggered = true;
                $queueTest->assertInstanceOf(MessageIterator::class, $e->getMessages());

                $e->stopAwait(true);
            }
        );

        //Ensure we have one message
        $this->queue->send('test');

        $this->assertInstanceOf(Queue::class, $this->queue->await());
        $this->assertTrue($eventReceiveTriggered, 'QueueEvent::EVENT_RECEIVE has been not triggered');
        $this->assertTrue($eventIdleTriggered, 'QueueEvent::EVENT_IDLE has been not triggered');

        //Cleanup
        $this->queue->getEventManager()->detach($receiveHandler);
        $this->queue->getEventManager()->detach($idleHandler);
    }


    public function testStopAwaitOnReceive()
    {
        if (!$this->queue->canAwait()) {
            $this->markTestSkipped('Method await() not supported');
        }

        $queueTest = $this;
        $eventReceiveTriggered = false;

        $receiveHandler = $this->queue->getEventManager()->attach(
            QueueEvent::EVENT_RECEIVE,
            function (QueueEvent $e) use ($queueTest, &$eventReceiveTriggered) {
                $eventReceiveTriggered = true;
                $e->stopAwait(true);
            }
        );

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
            $this->markTestSkipped('Method await() not supported');
        }

        $queueTest = $this;
        $triggerCount = 0;

        $idleHandler = $this->queue->getEventManager()->attach(
            QueueEvent::EVENT_IDLE,
            function (QueueEvent $e) use ($queueTest, &$triggerCount) {
                $triggerCount++;
                if ($triggerCount == 2) {
                    $e->stopAwait(true);
                }
            }
        );

        $this->queue->await();
        $this->assertEquals(2, $triggerCount, 'QueueEvent::EVENT_IDLE has been not triggered 2 times');

        //Cleanup
        $this->queue->getEventManager()->detach($idleHandler);
    }

    public function testAwaitUnsupported()
    {
        $q = clone $this->queue; //assume array adapter
        $q->getOptions()->setEnableAwaitEmulation(false);

        $this->setExpectedException(UnsupportedMethodCallException::class);
        $q->await();
    }

    public function testAwaitWithParamsAndCapableAdapter()
    {
        $mockAdapter = $this->getMock(Adapter\Capabilities\AwaitMessagesCapableInterface::class);

        $receiveParams = new ReceiveParameters();

        $q = new Queue('test', $mockAdapter);

        $mockAdapter->expects($this->any())->method('awaitMessages')->with(
            $this->equalTo($q),
            $this->equalTo(
                function () {
                }
            ),
            $this->equalTo($receiveParams)
        );

        $this->assertSame($q, $q->await($receiveParams));
    }


    public function testCountUnsupported()
    {
        $q = new Queue('test', new NullAdapter);

        $this->assertFalse($q->canCountMessages());

        $this->setExpectedException(UnsupportedMethodCallException::class);
        $q->count();
    }

    public function testDeleteMessageUnsupported()
    {
        $q = new Queue('test', new NullAdapter);

        $this->assertFalse($q->canDeleteMessage());

        $this->setExpectedException(UnsupportedMethodCallException::class);
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

        $mockAdapter = $this->getMock(Adapter\AdapterInterface::class);
        $mockAdapter->expects($this->any())->method('getAvailableSendParams')->will(
            $this->returnValue(
                [
                    SendParameters::SCHEDULE,
                    SendParameters::REPEATING_INTERVAL
                ]
            )
        );

        $q = new Queue('test', $mockAdapter);

        $mockAdapter->expects($this->any())->method('sendMessage')->with(
            $this->equalTo($q),
            $this->equalTo($message),
            $this->equalTo($expectedParams)
        )->will($this->returnValue($message));

        $this->assertTrue($q->isSendParamSupported(SendParameters::SCHEDULE));
        $this->assertTrue($q->isSendParamSupported(SendParameters::REPEATING_INTERVAL));

        $this->assertSame($message, $q->schedule($message, $time, $repeting));
    }

    public function testScheduleMessageUnsupported()
    {
        $q = new Queue('test', new NullAdapter);

        $this->assertFalse($q->isSendParamSupported(SendParameters::SCHEDULE));

        $this->setExpectedException(UnsupportedMethodCallException::class);
        $q->schedule(new Message());
    }

    public function testScheduleMessageRepeatingIntervalUnsupported()
    {
        $mockAdapter = $this->getMock(Adapter\AdapterInterface::class);
        $mockAdapter->expects($this->any())->method('getAvailableSendParams')->will(
            $this->returnValue(
                [
                    SendParameters::SCHEDULE
                ]
            )
        );
        $mockAdapter->expects($this->any())->method('sendMessage');

        /* @var $mockAdapter Adapter\AdapterInterface */
        $q = new Queue('test', $mockAdapter);

        $this->assertTrue($q->isSendParamSupported(SendParameters::SCHEDULE));
        $this->assertFalse($q->isSendParamSupported(SendParameters::REPEATING_INTERVAL));

        $this->setExpectedException(InvalidArgumentException::class);
        $q->schedule(new Message(), 1, 1);
    }

    public function testUnscheduleMessage()
    {
        $message = new Message();

        $providedOptions = [
            SendParameters::SCHEDULE => time() + 60,
            SendParameters::REPEATING_INTERVAL => 3600,
        ];

        $mockAdapter = $this->getMock(Adapter\Capabilities\DeleteMessageCapableInterface::class);
        $mockAdapter->expects($this->any())->method('getAvailableSendParams')->will(
            $this->returnValue(
                [
                    SendParameters::SCHEDULE,
                    SendParameters::REPEATING_INTERVAL
                ]
            )
        );

        $q = new Queue('test', $mockAdapter);
        $message->setMetadata($q->getOptions()->getMessageMetadatumKey(), ['options' => $providedOptions]);


        $mockAdapter->expects($this->any())->method('getMessageInfo')->with(
            $this->equalTo($q),
            $this->equalTo($message)
        )->will($this->returnValue(['options' => $providedOptions]));


        $mockAdapter->expects($this->any())->method('deleteMessage')->with(
            $this->equalTo($q),
            $this->equalTo($message)
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
        $q = new Queue('test', new NullAdapter);

        $this->assertFalse($q->isSendParamSupported(SendParameters::SCHEDULE));

        $this->setExpectedException(UnsupportedMethodCallException::class);
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
        $adapterMock = $this->getMock(Adapter\AdapterInterface::class);
        $adapterMock->expects($this->any())->method("getAvailableSendParams")->will($this->returnValue(['foo']));
        $q = new Queue('test', $adapterMock);
        $this->isTrue($q->isSendParamSupported('foo'));
        $this->isFalse($q->isSendParamSupported('bar'));
    }

    public function testIsReceiveParamSupported()
    {
        $adapterMock = $this->getMock(Adapter\AdapterInterface::class);
        $adapterMock->expects($this->any())->method('getAvailableReceiveParams')->will($this->returnValue(['foo']));
        $q = new Queue('test', $adapterMock);
        $this->isTrue($q->isReceiveParamSupported('foo'));
        $this->isFalse($q->isReceiveParamSupported('bar'));
    }

    public function testQueueIsEventManagerAware()
    {
        $this->assertInstanceOf(EventManagerAwareInterface::class, $this->queue);

        $defaultEventManager = $this->queue->getEventManager();
        $this->assertInstanceOf(EventManagerInterface::class, $defaultEventManager);

        $newEventManager = new EventManager();
        $this->assertInstanceOf(Queue::class, $this->queue->setEventManager($newEventManager));

        $this->assertSame($newEventManager, $this->queue->getEventManager());

        //Restore original manager
        $this->queue->setEventManager($defaultEventManager);
    }

    public function testGetSetEvent()
    {
        $defaultEvent = $this->queue->getEvent();
        $this->assertInstanceOf(QueueEvent::class, $defaultEvent);

        $newEvent = new Event();
        $newEvent->setParam('foo', 'bar');

        $this->assertInstanceOf(Queue::class, $this->queue->setEvent($newEvent));

        //Test recast
        $this->assertInstanceOf(QueueEvent::class, $this->queue->getEvent());
        $this->assertSame('bar', $this->queue->getEvent()->getParam('foo'));

        //Restore original event
        $this->queue->setEvent($defaultEvent);
    }

    public function testUsageExample()
    {
        // Create an array queue adapter
        $adapter = new ArrayAdapter;

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
            $this->assertStringStartsWith('queue', $name); // echo $name, "\n";
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
