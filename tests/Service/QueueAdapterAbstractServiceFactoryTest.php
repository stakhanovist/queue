<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Service;

use Stakhanovist\Queue\Adapter;
use Zend\ServiceManager\ServiceManager;

/**
 * Class QueueAdapterAbstractServiceFactoryTest
 *
 * @group service
 */
class QueueAdapterAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    public function setUp()
    {
        Adapter\AdapterFactory::resetAdapterPluginManager();

        $this->sm = new ServiceManager;
        $this->sm->setService(
            'Config',
            [
                'stakhanovist' =>
                    [
                        'queue_adapters' => [
                            'ArrayAdapter' => [
                                'adapter' => 'array',
                                'options' => ['dummyOption' => 'dummyValue'],
                            ],
                            'Foo' => [
                                'adapter' => 'array',
                                'options' => ['dummyOption' => 'dummyValue'],
                            ],
                        ]
                    ]
            ]
        );
        $this->sm->addAbstractFactory(QueueAdapterAbstractServiceFactory::class);
    }

    public function tearDown()
    {
        Adapter\AdapterFactory::resetAdapterPluginManager();
    }

    public function testCanLookupAdapterByName()
    {
        $this->assertTrue($this->sm->has('ArrayAdapter'));
        $this->assertTrue($this->sm->has('Foo'));
    }

    public function testCanRetrieveAdapterByName()
    {
        $adapterA = $this->sm->get('ArrayAdapter');
        $this->assertInstanceOf(Adapter\ArrayAdapter::class, $adapterA);

        $adapterB = $this->sm->get('Foo');
        $this->assertInstanceOf(Adapter\ArrayAdapter::class, $adapterB);

        $this->assertNotSame($adapterA, $adapterB);
    }

    public function testInvalidServiceNameWillBeIgnored()
    {
        $this->assertFalse($this->sm->has('invalid'));
    }

    public function testCanCreateServiceWithNameAndConfigEmpty()
    {
        $sm = new ServiceManager();
        $sm->setService('Config', null);
        $abstractFactory = new QueueAdapterAbstractServiceFactory();
        $this->isFalse($abstractFactory->canCreateServiceWithName($sm, 'foo', 'bar'));
    }

    public function testGetConfigNoKeyConfig()
    {
        $sm = new ServiceManager();
        $sm->setService('Config', 'foo');
        $abstractFactory = new QueueAdapterAbstractServiceFactory();
        $abstractFactory->canCreateServiceWithName($sm, 'foo', 'bar');
    }
}
