<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

use Stakhanovist\Queue\Adapter\ArrayAdapter;

/**
 * Class ArrayAdapterTest
 *
 * @group adapter
 */
class ArrayAdapterTest extends AdapterTest
{
    /**
     * Provide the adapter FQN
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return ArrayAdapter::class;
    }

    /**
     * Extra non standard test
     */
    public function testMagics()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $this->assertTrue(is_array($adapter->__sleep()));
        $data = serialize($adapter);
        $new = unserialize($data);
        $this->assertEquals($new->getData(), $adapter->getData());
    }

    /**
     * Extra non standard test
     */
    public function testGetSetData()
    {
        $queue = $this->createQueue(__FUNCTION__);
        $adapter = $queue->getAdapter();

        $data = ['test' => 1];
        $adapter->setData($data);
        $got = $adapter->getData();
        $this->assertEquals($data['test'], $got['test']);
    }
}
