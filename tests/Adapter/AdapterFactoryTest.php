<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest\Adapter;

use Zend\Stdlib\ArrayObject;
use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Queue;

/**
 *
 * @group      Stakhanovist_Queue
 */
class AdapterFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Adapter\AdapterFactory::resetAdapterPluginManager();
    }

    public function tearDown()
    {
        Adapter\AdapterFactory::resetAdapterPluginManager();
    }

    public function testDefaultAdapterPluginManager()
    {
        $adapters = Adapter\AdapterFactory::getAdapterPluginManager();
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\AdapterPluginManager', $adapters);
    }

    public function testChangeAdapterPluginManager()
    {
        $adapters = new Adapter\AdapterPluginManager();
        Adapter\AdapterFactory::setAdapterPluginManager($adapters);
        $this->assertSame($adapters, Adapter\AdapterFactory::getAdapterPluginManager());
    }

    public function testAdapterFactory()
    {
        $adapter1 = Adapter\AdapterFactory::factory(array(
            'adapter' => 'ArrayAdapter',
            'options' => array('dummyOption' => 'dummyValue'),
        ));
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\ArrayAdapter', $adapter1);

        $adapter2 = Adapter\AdapterFactory::factory(array(
            'adapter' => 'ArrayAdapter',
            'options' => array('dummyOption' => 'dummyValue'),
        ));
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\ArrayAdapter', $adapter2);

        $this->assertNotSame($adapter1, $adapter2);
    }


    public function testFactoryAdapterAsString()
    {
        $adapter = Adapter\AdapterFactory::factory(array(
            'adapter' => 'Null',
        ));
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\Null', $adapter);
    }

    public function testFactoryWithAdapterAsStringAndOptions()
    {
        $adapter = Adapter\AdapterFactory::factory(array(
            'adapter' => 'Null',
            'options' => array(
                'dummy' => 'test'
            ),
        ));

        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\Null', $adapter);
        $options = $adapter->getOptions();
        $this->assertSame('test', $options['dummy']);
    }

    public function testFactoryWithAdapterAsInstanceAndOptions()
    {
        $adapter = Adapter\AdapterFactory::factory(array(
            'adapter' => new Adapter\Null(),
            'options' => array(
                'dummy' => 'test'
            ),
        ));

        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\Null', $adapter);
        $options = $adapter->getOptions();
        $this->assertSame('test', $options['dummy']);
    }

    public function testFactoryAdapterIsInstanceOfTraversable()
    {
        $config = new \ArrayObject();
        $config['adapter'] = 'Null';
        $config['options'] = array(
            'dummy' => 'test'
        );
        $adapter = Adapter\AdapterFactory::factory($config);
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\Null', $adapter);
        $options = $adapter->getOptions();
        $this->assertSame('test', $options['dummy']);
    }

    public function testFactoryAdapterInvalidArgument()
    {
        $this->setExpectedException("Stakhanovist\Queue\Exception\InvalidArgumentException");
        $config = "dummy";
        $adapter = Adapter\AdapterFactory::factory($config);
    }

    public function testFactoryAdapterInvalidArgumentAdapterKeyNotFound()
    {
        $this->setExpectedException("Stakhanovist\Queue\Exception\InvalidArgumentException");
        $config = new \ArrayObject();
        $config['options'] = array(
            'dummy' => 'test'
        );
        $adapter = Adapter\AdapterFactory::factory($config);
    }

    public function testFactoryAdapterInvalidArgumentOptionsIsntArray()
    {
        $this->setExpectedException("Stakhanovist\Queue\Exception\InvalidArgumentException");
        $config = new \ArrayObject();
        $config['adapter'] = 'Null';
        $config['options'] = 'string';
        $adapter = Adapter\AdapterFactory::factory($config);
    }
}
