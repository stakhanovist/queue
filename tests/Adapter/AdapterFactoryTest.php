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
use Stakhanovist\Queue\Exception\InvalidArgumentException;

/**
 * Class AdapterFactoryTest
 *
 * @group adapters
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
        $this->assertInstanceOf(Adapter\AdapterPluginManager::class, $adapters);
    }

    public function testChangeAdapterPluginManager()
    {
        $adapters = new Adapter\AdapterPluginManager;
        Adapter\AdapterFactory::setAdapterPluginManager($adapters);
        $this->assertSame($adapters, Adapter\AdapterFactory::getAdapterPluginManager());
    }

    public function testAdapterFactory()
    {
        $adapter1 = Adapter\AdapterFactory::factory(
            [
            'adapter' => 'array',
            'options' => ['dummyOption' => 'dummyValue'],
            ]
        );
        $this->assertInstanceOf(Adapter\ArrayAdapter::class, $adapter1);

        $adapter2 = Adapter\AdapterFactory::factory(
            [
            'adapter' => 'array',
            'options' => ['dummyOption' => 'dummyValue'],
            ]
        );
        $this->assertInstanceOf(Adapter\ArrayAdapter::class, $adapter2);

        $this->assertNotSame($adapter1, $adapter2);
    }


    public function testFactoryAdapterAsString()
    {
        $adapter = Adapter\AdapterFactory::factory(
            [
            'adapter' => 'null',
            ]
        );
        $this->assertInstanceOf(Adapter\NullAdapter::class, $adapter);
    }

    public function testFactoryWithAdapterAsStringAndOptions()
    {
        $adapter = Adapter\AdapterFactory::factory(
            [
            'adapter' => 'null',
            'options' => [
                'dummy' => 'test'
            ],
            ]
        );

        $this->assertInstanceOf(Adapter\NullAdapter::class, $adapter);
        $options = $adapter->getOptions();
        $this->assertSame('test', $options['dummy']);
    }

    public function testFactoryWithAdapterAsInstanceAndOptions()
    {
        $adapter = Adapter\AdapterFactory::factory(
            [
            'adapter' => new Adapter\NullAdapter,
            'options' => [
                'dummy' => 'test'
            ],
            ]
        );

        $this->assertInstanceOf(Adapter\NullAdapter::class, $adapter);
        $options = $adapter->getOptions();
        $this->assertSame('test', $options['dummy']);
    }

    public function testFactoryAdapterIsInstanceOfTraversable()
    {
        $config = new \ArrayObject;
        $config['adapter'] = 'null';
        $config['options'] = [
            'dummy' => 'test'
        ];
        $adapter = Adapter\AdapterFactory::factory($config);
        $this->assertInstanceOf(Adapter\NullAdapter::class, $adapter);
        $options = $adapter->getOptions();
        $this->assertSame('test', $options['dummy']);
    }

    public function testFactoryAdapterInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $config = "dummy";
        Adapter\AdapterFactory::factory($config);
    }

    public function testFactoryAdapterInvalidArgumentAdapterKeyNotFound()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $config = new \ArrayObject;
        $config['options'] = [
            'dummy' => 'test'
        ];
        Adapter\AdapterFactory::factory($config);
    }

    public function testFactoryAdapterInvalidArgumentOptionsIsntArray()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $config = new \ArrayObject;
        $config['adapter'] = 'Null';
        $config['options'] = 'string';
        Adapter\AdapterFactory::factory($config);
    }
}
