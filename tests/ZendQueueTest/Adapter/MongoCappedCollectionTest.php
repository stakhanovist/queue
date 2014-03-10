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

    use ZendQueue\Adapter\MongoCappedCollection;
use ZendQueue\Adapter\MongoCollection;
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
        $queue = $this->createQueue(__FUNCTION__);
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
}
