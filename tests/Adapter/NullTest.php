<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

    use Stakhanovist\Queue\Adapter\Null;
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
class NullTest extends AdapterTest
{

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
           'getQueueId', 'queueExists',
        );
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
        return 'Null';
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

    public function getTestConfig()
    {
        return array('driverOptions' => array());
    }

    public function testGetQueueId()
    {
        $null = new Null();
        $this->assertNull($null->getQueueId('foo'));
    }

    public function testQueueExists()
    {
        $null = new Null();
        $this->assertFalse($null->queueExists('foo'));
    }

}
