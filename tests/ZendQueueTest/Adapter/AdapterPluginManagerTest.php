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

use Zend\ServiceManager\Config;
use Zend\Stdlib\ArrayObject;
use ZendQueue\Adapter;

/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage UnitTests
 * @group      Zend_Queue
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
                'null' => 'ZendQueue\Adapter\Null',
            )
        )));
    }

    public function testAddAdapterThatImplementAdapterInterface()
    {
        $adapter = $this->getMock("ZendQueue\Adapter\Null");
        $this->assertNull($this->pluginManager->validatePlugin($adapter));
    }

    public function testAddString()
    {
        $this->setExpectedException("ZendQueue\Exception\RuntimeException");
        $adapter = "i'm not an adapter";
        $this->assertNull($this->pluginManager->validatePlugin($adapter));
    }
}