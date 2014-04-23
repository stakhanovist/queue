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

use ZendQueue\Adapter\Capabilities\AwaitMessagesCapableInterface;
use ZendQueue\Adapter\MongoCollection;
use ZendQueue\Message\Message;

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
     * 'Zend_Queue_Adapter_' . $this->getAdapterName()
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return '\ZendQueue\Adapter\\' . $this->getAdapterName();
    }

    public function getTestOptions()
    {
        return array('driverOptions' => array(
            'db' => 'zendqueue_test_capped'
        ));
    }

    public function testShouldThrowExceptionOnExistingNonCappedCollection()
    {
        $mongoNonCappedAdapter = new MongoCollection();
        $mongoNonCappedAdapter->setOptions($this->getTestOptions());
        $mongoNonCappedAdapter->connect();
        $mongoNonCappedAdapter->createQueue(__FUNCTION__);

        $this->setExpectedException('ZendQueue\Exception\RuntimeException');
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
     * @expectedException \ZendQueue\Exception\RuntimeException
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
     * @expectedException \ZendQueue\Exception\RuntimeException
     */
    public function testAwaitMessagesWithoutSecondLast()
    {
        $queue = $this->createQueue(__FUNCTION__);
        /** @var \ZendQueue\Adapter\MongoCappedCollection $adapter */
        $adapter = $queue->getAdapter();
        $this->checkAdapterSupport($adapter, array('sendMessage', 'deleteQueue'));

        $receiveCount = 0;
        $messages = null;

        $queue->send('foo');

        /** @var MongoCollection $collection */
        $collection = $adapter->getMongoDb()->selectCollection($queue->getName());
        $collection->drop();

        $adapter->awaitMessages($queue, function ($msgs) use (&$receiveCount, &$messages, $queue) {
            return false;
        });

    }
}
