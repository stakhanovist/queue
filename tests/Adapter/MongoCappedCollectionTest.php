<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

use Stakhanovist\Queue\Adapter\MongoCollection;
use Stakhanovist\Queue\Message\Message;

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
class MongoCappedCollectionTest extends AdapterTest
{

    public function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('The mongo PHP extension is not available');
        }
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
        return 'MongoCappedCollection';
    }

    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You may overload this method.  The default return is
     * 'Stakhanovist_Queue_Adapter_' . $this->getAdapterName()
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return '\Stakhanovist\Queue\Adapter\\' . $this->getAdapterName();
    }

    public function getTestOptions()
    {
        return array('driverOptions' => array(
            'db' => 'stakhanovist_queue_test_capped'
        ));
    }

    public function testShouldThrowExceptionOnExistingNonCappedCollection()
    {
        $mongoNonCappedAdapter = new MongoCollection();
        $mongoNonCappedAdapter->setOptions($this->getTestOptions());
        $mongoNonCappedAdapter->connect();
        $mongoNonCappedAdapter->createQueue(__FUNCTION__);

        $this->setExpectedException('Stakhanovist\Queue\Exception\RuntimeException');
        $this->createQueue(__FUNCTION__);
    }

    public function testSendMessageShouldThrowExcepetionWhenQueueDoesntExist()
    {
        $this->markTestSkipped('Mongo does not throw execption if collection does not exists');
    }

    public function testDeleteMessageShouldThrowExcepetionWhenQueueDoesntExist()
    {
        $this->markTestSkipped('Mongo does not throw execption if collection does not exists');
    }

    public function testCountMessageShouldThrowExcepetionWhenQueueDoesntExist()
    {
        $this->markTestSkipped('Mongo does not throw execption if collection does not exists');
    }

    /**
     * @expectedException \Stakhanovist\Queue\Exception\RuntimeException
     */
    public function testSendMessageWithFullCappedCollection()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();
        $options = $adapter->getOptions();
        $options['threshold'] = 10000;
        $adapter->setOptions($options);
        $this->checkAdapterSupport($adapter, 'sendMessage');
        $adapter->sendMessage($queue, new Message());
    }

    /**
     * @expectedException \Stakhanovist\Queue\Exception\RuntimeException
     */
    public function testAwaitMessagesWithoutSecondLast()
    {
        $queue = $this->createQueue(__FUNCTION__);
        /** @var \Stakhanovist\Queue\Adapter\MongoCappedCollection $adapter */
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'deleteQueue'));

        $receiveCount = 0;
        $messages = null;

        $queue->send('foo');

        /** @var MongoCollection $collection */
        $collection = $adapter->getMongoDb()->selectCollection($queue->getName());
        $collection->drop();

        $adapter->awaitMessages($queue, function () use (&$receiveCount, &$messages, $queue) {
            return false;
        });
    }
}
