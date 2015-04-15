<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

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
class ArrayTest extends AdapterTest
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
        return 'ArrayAdapter';
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

    // extra non standard tests
    public function test_magic()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $this->assertTrue(is_array($adapter->__sleep()));
        $data = serialize($adapter);
        $new = unserialize($data);
        $this->assertEquals($new->getData(), $adapter->getData());
    }

    public function test_get_setData()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $data = array('test' => 1);
        $adapter->setData($data);
        $got = $adapter->getData();
        $this->assertEquals($data['test'], $got['test']);
    }
}
