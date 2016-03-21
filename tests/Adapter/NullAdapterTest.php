<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

use Stakhanovist\Queue\Adapter\NullAdapter;

/**
 * Class NullAdapterTest
 *
 * @group adapter
 */
class NullAdapterTest extends AdapterTest
{
    /**
     * Return the list of base test supported
     *
     * @return array
     */
    protected function getSupportedTests()
    {
        return [
           'getQueueId', 'queueExists',
        ];
    }

    /**
     * Get the adapter FQN
     *
     * @return string
     */
    public function getAdapterFullName()
    {
        return NullAdapter::class;
    }

    /**
     * @return array
     */
    public function getTestConfig()
    {
        return ['driverOptions' => []];
    }

    public function testGetQueueId()
    {
        $null = new NullAdapter;
        $this->assertNull($null->getQueueId('foo'));
    }

    public function testQueueExists()
    {
        $null = new NullAdapter;
        $this->assertFalse($null->queueExists('foo'));
    }
}
