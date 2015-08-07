<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Exception\RuntimeException;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AdapterPluginManagerTest
 *
 * @group adapters
 */
class AdapterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter\AdapterPluginManager
     */
    protected $pluginManager;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = new ServiceManager;
        $this->pluginManager = new Adapter\AdapterPluginManager(
            new Config(
                [
                    'invoke' => [
                        'null' => Adapter\NullAdapter::class,
                    ]
                ]
            )
        );
    }

    public function testAddAdapterThatImplementAdapterInterface()
    {
        $adapter = $this->getMock(Adapter\NullAdapter::class);
        $this->assertNull(
            $this->pluginManager->validatePlugin($adapter)
        );
    }

    public function testAddString()
    {
        $this->setExpectedException(RuntimeException::class);
        $adapter = 'I\'m not an adapter mate';
        $this->assertNull(
            $this->pluginManager->validatePlugin($adapter)
        );
    }
}
