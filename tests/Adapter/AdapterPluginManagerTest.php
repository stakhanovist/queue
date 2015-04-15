<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

use Zend\ServiceManager\Config;
use Zend\Stdlib\ArrayObject;
use Stakhanovist\Queue\Adapter;

/**
 *
 * @group      Stakhanovist_Queue
 */
class AdapterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $pluginManager;
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = new \Zend\ServiceManager\ServiceManager();
        $this->pluginManager = new Adapter\AdapterPluginManager(new Config(array(
            'invoke' => array(
                'null' => 'Stakhanovist\Queue\Adapter\Null',
            )
        )));
    }

    public function testAddAdapterThatImplementAdapterInterface()
    {
        $adapter = $this->getMock("Stakhanovist\Queue\Adapter\Null");
        $this->assertNull($this->pluginManager->validatePlugin($adapter));
    }

    public function testAddString()
    {
        $this->setExpectedException("Stakhanovist\Queue\Exception\RuntimeException");
        $adapter = "i'm not an adapter";
        $this->assertNull($this->pluginManager->validatePlugin($adapter));
    }
}
