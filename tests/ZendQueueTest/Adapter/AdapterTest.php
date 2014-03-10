<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueueTest\Adapter;

use Zend\Config;
use ZendQueue\Adapter;
use ZendQueue\Message\Message;
use ZendQueue\Queue;
use ZendQueue\Adapter\AdapterInterface;
use ZendQueue\QueueOptions;
use ZendQueue\Message\MessageIterator;
use ZendQueue\Parameter\ReceiveParameters;
use ZendQueue\Adapter\AdapterFactory;
use ZendQueue\Adapter\Capabilities\DeleteMessageCapableInterface;
use ZendQueue\Adapter\Capabilities\ListQueuesCapableInterface;
use ZendQueue\Adapter\Capabilities\CountMessagesCapableInterface;
use ZendQueue\Adapter\Null;

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
abstract class AdapterTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        $this->error = false;
    }

    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You must overload this method
     *
     * @return string
     */
    public function getAdapterName()
    {
        $this->fail('You must overload this function: getAdapterName()');

        // example for \ZendQueue\Adatper\ArrayAdapter
        return 'ArrayAdapter';
    }

    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You may overload this method.  The default return is
     * 'Zend_Queue_Adapter_' . $this->getAdapterName()
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return '\ZendQueue\Adapter\\' . $this->getAdapterName();
    }

    /**
     * return the list of base test supported.
     * If some special adapter doesnt' support one of these, this method should be ovveriden
     * So test will expect an UnsupportedMethodCallException
     *
     * @return array
     */
    public function getSupportedTests()
    {
        return array(
            'createQueue', 'deleteQueue', 'sendMessage', 'receiveMessages', 'getMessageInfo'
        );
    }

    public function getTestOptions()
    {
        return array('driverOptions' => array());
    }

    /**
     * for ActiveMQ it uses /queue/ /temp-queue/ /topic/ /temp-topic/
     */
    public function createQueueName($name)
    {
        return $name;
    }

    /**
     * This is a generic function that creates a queue
     *
     * @param array $config, $config['name'] must be set.
     *
     * or
     *
     * @param string $name - name of the queue to create
     * @param QueueOptions $options
     * @return Queue
     */
    protected function createQueue($name, QueueOptions $options = null)
    {

        $adapter = AdapterFactory::factory(array(
            'adapter' => $this->getAdapterFullName(),
            'options' => $this->getTestOptions(),
        ));

        $queue = new Queue($this->createQueueName($name), $adapter, $options);

        if (!$adapter instanceof Adapter\Null) {
             $queue->ensureQueue();
        }

        return $queue;
    }

    /**
     * @param AdapterInterface $adapter
     * @param array|string $needles
     * @return boolean
     */
    protected function checkAdapterSupport(AdapterInterface $adapter, $needles)
    {
        if (is_string($needles)) {
            $needles = array($needles);
        }

        $supported = $this->getSupportedTests();

        $hasSupport = true;
        foreach ($needles as $needle) {
            if (!in_array($needle, $supported)) {
                $hasSupport = false;
                break;
            }
        }

        if (!$hasSupport) {
            $this->setExpectedException('ZendQueue\Exception\UnsupportedMethodCallException');
        }

        return true;
    }

    protected function checkMessageInfo($info, Queue $queue)
    {
        $this->assertInternalType('array', $info);

        $this->assertArrayHasKey('adapter', $info);
        $this->assertSame(get_class($queue->getAdapter()), $info['adapter']);

        $this->assertArrayHasKey('queueName', $info);
        $this->assertSame($queue->getName(), $info['queueName']);
    }


    public function testSetGetOptions()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'deleteQueue');

        $adapterOptions = array(
            'dummy' => 'dummyValue'
        );


        $adapter->setOptions($adapterOptions);

        $new = $adapter->getOptions();

        $this->assertTrue(is_array($new));
        $this->assertEquals($adapterOptions['dummy'], $new['dummy']);

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testGetAvailableSendParams()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'deleteQueue');

        $this->assertInternalType('array', $adapter->getAvailableSendParams());

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testGetAvailableReceiveParams()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'deleteQueue');

        $this->assertInternalType('array', $adapter->getAvailableReceiveParams());

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    // test the constructor
    public function testZendQueueAdapterConstructor()
    {
        $class = $this->getAdapterFullName();

        try {
            $obj = new $class(\true);
            $this->fail('__construct() $config must be an array');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $obj = new $class(array());
            $this->fail('__construct() cannot accept an empty array for a configuration');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $obj = new $class(array('name' => 'queue1', 'driverOptions' => \true));
            $this->fail('__construct() $config[\'options\'] must be an array');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $obj = new $class(array('name' => 'queue1', 'driverOptions' => array('opt' => 'val')));
            $this->fail('__construct() humm I think this test is supposed to work @TODO');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        try {
            $config = new Config\Config(array('driverOptions' => array()));
            $obj = new $class($config);
            $this->fail('__construct() \'name\' is a required configuration value');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $config = new Config\Config(array('name' => 'queue1', 'driverOptions' => array(), 'options' => array('opt1' => 'val1')));
            $obj = new $class($config);
            $this->fail('__construct() is not supposed to accept a true value for a configuraiton');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // try passing the queue to the $adapter
        if (!$queue = $this->createQueue(__FUNCTION__)) {
            return;
        }
        $obj = new $class();
        $this->assertTrue($obj instanceof Adapter\AbstractAdapter);
    }

    // this tests the configuration option of messageClass (\ZendQueue\Message\Message by default)
    public function testZendQueueMessageTest()
    {
        $options = new QueueOptions();

        $this->assertEquals('\ZendQueue\Message\Message', $options->getMessageClass());

        $queue = $this->createQueue(__FUNCTION__, $options);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        $body = 'this is a test message';
        $message = new Message();
        $message->setContent($body);
        $queue->send($message);

        $this->assertTrue($message instanceof Message);

        $list = $queue->receive();
        $this->assertTrue($list instanceof MessageIterator);
        foreach ($list as $i => $message) {
            $this->assertTrue($message instanceof Message);
            if ($adapter instanceof DeleteMessageCapableInterface) {
                $queue->delete($message);
            }
        }

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testFactory()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $this->assertTrue($queue->getAdapter() instanceof Adapter\AbstractAdapter);

        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'deleteQueue');

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testConnect()
    {
        $queue = $this->createQueue(__FUNCTION__);

        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'deleteQueue');

        $this->assertTrue($adapter->connect());

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testGetQueueId()
    {
        $queue = $this->createQueue(__FUNCTION__);

        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'deleteQueue');

        //test existing queue
        $this->assertNotEmpty($adapter->getQueueId($queue->getName()));

        //test non-existing queue
        $this->assertNull($adapter->getQueueId('non-existing queue'));

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testCreate()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('createQueue', 'deleteQueue'));

        // cannot recreate a queue.
        $this->assertFalse($adapter->createQueue($queue->getName()));

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testDelete()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('createQueue', 'deleteQueue'));

        $new = $this->createQueueName(__FUNCTION__ . '_2');
        $this->assertTrue($adapter->createQueue($new));
        $this->assertTrue($adapter->deleteQueue($new));

        if ($adapter instanceof ListQueuesCapableInterface) {
            if (in_array($new, $adapter->listQueues())) {
                $this->fail('delete() failed to delete it\'s queue, but returned true: ' . $new);
            }
        }

        $this->assertFalse($adapter->deleteQueue('non-existing-queue'));

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testIsExists()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        if ($adapter instanceof Null) {
            $this->assertFalse($adapter->queueExists($queue->getName()));
            return;
        }

        $this->checkAdapterSupport($adapter, array('createQueue', 'deleteQueue'));


        $this->assertFalse($adapter->queueExists('perl'));

        $new = $this->createQueueName(__FUNCTION__ . '_3');
        $this->assertTrue($adapter->createQueue($new));
        $this->assertTrue($adapter->queueExists($new));
        $this->assertTrue($adapter->deleteQueue($new));

        if ($adapter instanceof ListQueuesCapableInterface) {
            if (in_array($new, $adapter->listQueues())) {
                $this->fail('delete() failed to delete it\'s queue, but returned true: ' . $new);
            }
        }

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testSend()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        $body = 'this is a test message';
        $message = new Message();
        $message->setContent($body);

        $this->assertInstanceOf('Zend\Stdlib\MessageInterface', $adapter->sendMessage($queue, $message));

        /* @var MessageIterator $messages */
        $messages = $adapter->receiveMessages($queue);
        $this->assertInstanceOf('ZendQueue\Message\MessageIterator', $messages);
        foreach ($messages as $message) {
            $this->assertTrue($message instanceof Message);
            if ($adapter instanceof DeleteMessageCapableInterface) {
                $queue->delete($message);
            }
        }


        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testSendMessageShouldThrowExcepetionWhenQueueDoesntExist()
    {
        $this->setExpectedException('ZendQueue\Exception\QueueNotFoundException');

        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, 'sendMessage');

        $nonExistingQueue = new Queue('non-existing-queue', $adapter);
        $adapter->sendMessage($nonExistingQueue, new Message);
    }

    public function testReceive()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));


        // send the message
        $body = 'this is a test message 2';
        $message = new Message();
        $message->setContent($body);

        $adapter->sendMessage($queue, $message);

        // get it back
        $messages = $adapter->receiveMessages($queue, 1);
        $this->assertInstanceOf('ZendQueue\Message\MessageIterator', $messages);
        $this->assertEquals(1, $messages->count());
        $this->assertTrue($messages->valid());

        $message = $messages->current();
        $adapter->deleteMessage($queue, $messages->current());


        $this->assertTrue($message instanceof Message);
        $this->assertEquals($message->getContent(), $body);

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }


    public function testMessageInfo()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        $infoKey = $queue->getOptions()->getMessageMetadatumKey();
        $body = 'this is a test message';
        $message = new Message();
        $message->setContent($body);
        $message->setMetadata($infoKey, 'foo');

        $adapter->sendMessage($queue, $message);

        $messageInfo = $message->getMetadata($infoKey);

        //test message was cleaned
        $this->assertNotEquals('foo', $messageInfo);

        //test messageInfo is ok after send
        $this->checkMessageInfo($messageInfo, $queue);


        $message = $adapter->receiveMessages($queue)->current();
        $messageInfo = $message->getMetadata($infoKey);

        //test messageInfo is ok after receive
        $this->checkMessageInfo($messageInfo, $queue);

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }


    public function testDeleteMessage()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        // check to see if this function is supported
        if (!$adapter instanceof DeleteMessageCapableInterface) {
            $this->markTestSkipped('deleteMessage() is not supported');
            return;
        }

        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'sendMessage'));


        $body = 'this is a test message';
        $message = new Message();
        $message->setContent($body);
        if (!$adapter->sendMessage($queue, $message)) {
            $this->fail('sendMessage() failed');
        }
        $this->assertTrue($message instanceof Message);

        $messages = $adapter->receiveMessages($queue);
        $this->assertInstanceOf('ZendQueue\Message\MessageIterator', $messages);
        $this->assertTrue($messages->valid());

        $message = $messages->current();
        $this->assertTrue($message instanceof Message);

        $this->assertTrue($adapter->deleteMessage($queue, $message));



        //Test delete non-existing message
        $newMessage = new Message();
        $this->assertFalse($adapter->deleteMessage($queue, $newMessage));

//         // no more messages, should return false
//         // stomp and amazon always return true.
//         $falsePositive = array('Activemq', 'Amazon');
//         if (!in_array($this->getAdapterName(), $falsePositive)) {
//             $this->assertFalse($adapter->deleteMessage($queue, $message));
//         }

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testDeleteMessageShouldThrowExcepetionWhenQueueDoesntExist()
    {
        $this->setExpectedException('ZendQueue\Exception\QueueNotFoundException');

        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        // check to see if this function is supported
        if (!$adapter instanceof DeleteMessageCapableInterface) {
            $this->markTestSkipped('deleteMessage() is not supported');
            return;
        }

        $nonExistingQueue = new Queue('non-existing-queue', $adapter);
        $adapter->deleteMessage($nonExistingQueue, new Message);
    }

    public function testListQueues()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        // check to see if this function is supported
        if (!$adapter instanceof ListQueuesCapableInterface) {
            $this->markTestSkipped('listQueues() is not supported');
            return;
        }

        $this->checkAdapterSupport($adapter, 'deleteQueue');

        // get a listing of queues
        $queues = $adapter->listQueues();

        // this is an array right?
        $this->assertTrue(is_array($queues));

        // make sure our current queue is in this list.

        $this->assertTrue($adapter->queueExists($queue->getName()));


        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testCountMessages()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        // check to see if this function is supported
        if (!$adapter instanceof CountMessagesCapableInterface) {
            $this->markTestSkipped('countMessages() is not supported');
            return;
        }

        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        // for a test case, the count should be zero at first.
        $this->assertEquals($adapter->countMessages($queue), 0);


        $body = 'this is a test message';
        // send a message
        $message = new Message();
        $message->setContent($body);
        if (!$adapter->sendMessage($queue, $message)) {
            $this->fail('sendMessage() failed');
        }

        // test queue count for being 1
        $this->assertEquals($adapter->countMessages($queue), 1);

        // receive the message
        $message = $adapter->receiveMessages($queue);

        /* we need to delete the messages we put in the queue before
         * counting.
         *
         * not all adapters support deleteMessage, but we should remove
         * the messages that we created if we can.
         */
        if ($adapter instanceof DeleteMessageCapableInterface) {
            foreach ($message as $msg) {
                $adapter->deleteMessage($queue, $msg);
            }

            // test the count for being 0
            $this->assertEquals($adapter->countMessages($queue), 0);
        }


        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testCountMessageShouldThrowExcepetionWhenQueueDoesntExist()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        // check to see if this function is supported
        if (!$adapter instanceof CountMessagesCapableInterface) {
            $this->markTestSkipped('countMessages() is not supported');
            return;
        }

        $this->setExpectedException('ZendQueue\Exception\QueueNotFoundException');

        $nonExistingQueue = new Queue('non-existing-queue', new Null());
        $adapter->countMessages($nonExistingQueue);
    }

    /*
     * Send about 10 messages, read 5 back, then read 5 back 1 at a time.
     * delete all messages and created queue
     */
    public function testSampleBehavior()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $this->assertInstanceOf('ZendQueue\Queue', $queue);
        $this->assertInstanceOf('ZendQueue\Adapter\AdapterInterface', $adapter);

        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        for ($i = 0; $i < 10; $i++) {
            if (!$queue->send("{$i}")) {
                $this->fail("send() failed");
            }
        }

        $messages = $queue->receive(5);
        foreach ($messages as $i => $message) {
            $this->assertEquals($i, $message->getContent());
            if ($adapter instanceof DeleteMessageCapableInterface) {
                $queue->delete($message);
            }
        }

        $this->assertEquals(5, count($queue));

        for ($i = 5; $i < 10; $i++) {
            $messages = $queue->receive();
            $message = $messages->current();
            $this->assertEquals("{$i}", $message->getContent());
            if ($adapter instanceof DeleteMessageCapableInterface) {
                $queue->delete($message);
            }
        }

        if ($adapter instanceof CountMessagesCapableInterface) {
            $this->assertEquals(0, count($queue));
        }

        $this->assertTrue($queue->deleteQueue());
    }

    /**
     * This tests to see if a message is in-visibile for the proper amount of time
     *
     * usually adapters that support deleteMessage() by nature will support visibility
     */
    public function testVisibilityTimeout()
    {
        $debug = false;
        $default_timeout = 3; // how long we tell the queue to keep the message invisible
        $extra_delay = 2; // how long we are willing to wait for the test to finish before failing
        // keep in mind that some queue services are on forigen machines and need network time.

        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        if (!$queue->isReceiveParamSupported(ReceiveParameters::VISIBILITY_TIMEOUT)) {
            $adapter->deleteQueue($queue->getName());
            $this->markTestSkipped($this->getAdapterName() . ' does not support visibility of messages');
            return;
        }

        $body = 'hello world';

        $queue->send($body);
        $reciveParams = new ReceiveParameters();
        $reciveParams->setVisibilityTimeout($default_timeout);
        $messages = $queue->receive(1, $reciveParams); // messages are deleted at the bottom.

        if ($adapter instanceof CountMessagesCapableInterface) {
            $this->assertEquals(1, $queue->count());
        }

        $start = microtime(true);
        $end = 0;

        $this->assertTrue($messages instanceof MessageIterator);

        $timeout = $start + $default_timeout + $extra_delay;
        $found = false;
        $check = microtime(true);

        $end = false;
        do {
            $search = $queue->receive(1);
            if ((microtime(true) - $check) > 0.1) {
                $check = microtime(true);
                if ($debug) echo "Checking - found ", count($search), " messages at : ", $check, "\n";
            }
            if (count($search) > 0) {
                if ($search->current()->getContent() == $body) {
                    $found = true;
                    $end = microtime(true);
                } else {
                    $this->fail('sent message is not the message received');
                }
            }
        } while ($found === false && microtime(true) < $timeout);

        // record end time
        if ($end === false) {
            $end = microtime(true);
        }

        $duration = sprintf("%5.2f seconds", $end - $start);
        /*
        There has to be some fuzzyness regarding comparisons because while
        the timeout may be honored, the actual code time, database querying
        and so on, may take more than the timeout time.
        */
        if ($found) {
            if (abs($end - $start - $default_timeout) < $extra_delay) { // stupid Db Adapter responds in a fraction less than a second.
                $this->assertTrue(true, 'message was invisible for the required amount of time');
            } else {
                if ($debug) echo 'Duration: ', $duration, "\n";
                $this->fail('message was NOT invisible for the required amount of time');
            }
        } else {
            $this->fail('message never became visibile duration:' . $duration);
        }
        if ($debug) echo "duration $duration\n";

        // now we delete the messages
        if ($adapter instanceof DeleteMessageCapableInterface) {
            foreach ($messages as $msg) {
                $adapter->deleteMessage($queue, $msg);
            }
        }


        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }


    public function testClassFilter()
    {

        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));

        if (!$queue->isReceiveParamSupported(ReceiveParameters::CLASS_FILTER)) {
            $adapter->deleteQueue($queue->getName());
            $this->markTestSkipped($this->getAdapterName() . ' does not support class filter');
            return;
        }


        $body = 'hello world';

        //Test filter matches
        $queue->send($body);
        $reciveParams = new ReceiveParameters();
        $reciveParams->setClassFilter($queue->getOptions()->getMessageClass());
        $messages = $queue->receive(1, $reciveParams);

        $this->assertInstanceOf('\ZendQueue\Message\MessageIterator', $messages);
        $this->assertEquals(1, $messages->count());
        $this->assertInstanceOf($reciveParams->getClassFilter(), $messages->current());


        //Reset the queue
        $adapter->deleteQueue($queue->getName());
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();


        //Test filter doesnt' match
        $queue->send($body);
        $reciveParams = new ReceiveParameters();
        $reciveParams->setClassFilter('Zend\Stdlib\Message'); //Another class

        $messages = $queue->receive(1, $reciveParams);

        $this->assertInstanceOf('\ZendQueue\Message\MessageIterator', $messages);

        $this->assertEquals(0, $messages->count());

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testAdapterShouldReturnNoMessagesWhenZeroCountRequested()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'receiveMessages', 'deleteQueue'));
        $queue->send('My Test Message 1');
        $queue->send('My Test Message 2');

        $messages = $adapter->receiveMessages($queue, 0);
        $this->assertEquals(0, count($messages));

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }

    public function testAdapterShouldReturnNoMessageWhenNewQueue()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('receiveMessages', 'deleteQueue'));

        $messages = $adapter->receiveMessages($queue);
        $this->assertEquals(0, count($messages));

        // delete the queue we created
        $adapter->deleteQueue($queue->getName());
    }
}
