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
use Stakhanovist\Queue\Adapter\ArrayAdapter;
use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\QueueOptions;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Message as ZendMessage;

/**
 * Class QueueAbstractServiceFactoryTest
 *
 * @group service
 */
class QueueAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    public function setUp()
    {
        $adapter = new ArrayAdapter;
        $adapter->setOptions(['dummyOption' => 'dummyValue']);

        $this->sm = new ServiceManager;
        $this->sm->setInvokableClass('custom', Adapter\NullAdapter::class);
        $this->sm->setService(
            'Config',
            [
                'stakhanovist' => [
                    'queues' => [
                        'queueA' => [
                            'name' => 'A',
                            'adapter' => [ // Adapter as config
                                'adapter' => 'array',
                                'options' => ['dummyOption' => 'dummyValue'],
                            ],
                            'options' => ['messageClass' => ZendMessage::class],
                        ],
                        'queueB' => [
                            'name' => 'B',
                            'adapter' => $adapter, // Adapter as instance
                            'options' => ['messageClass' => ZendMessage::class],
                        ],
                        'queueC' => [
                            'name' => 'C',
                            'adapter' => 'custom',
                            'options' => ['messageClass' => ZendMessage::class],
                        ],
                    ]
                ]
            ]
        );
        $this->sm->addAbstractFactory(QueueAbstractServiceFactory::class);
    }

    public function testCanLookupQueueByName()
    {
        $this->assertTrue($this->sm->has('queueA'));
        $this->assertTrue($this->sm->has('queueB'));
    }

    public function testCanRetrieveQueueByName()
    {
        $qA = $this->sm->get('queueA');
        $this->assertInstanceOf(Queue::class, $qA);

        $qB = $this->sm->get('queueB');
        $this->assertInstanceOf(Queue::class, $qB);

        $this->assertNotSame($qA, $qB);
    }

    public function testConfiguration()
    {
        $qA = $this->sm->get('queueA');
        $this->assertInstanceOf(ArrayAdapter::class, $qA->getAdapter());

        if ($qA->getOptions() instanceof Adapter\AdapterInterface) {
            $options = $qA->getAdapter()->getOptions();
            $this->assertEquals('dummyValue', $options['dummyOption']);
        }

        $this->assertInstanceOf(QueueOptions::class, $qA->getOptions());
        if ($qA->getOptions() instanceof QueueOptions) {
            $this->assertEquals(ZendMessage::class, $qA->getOptions()->getMessageClass());
        }


        $qB = $this->sm->get('queueB');
        $this->assertInstanceOf(ArrayAdapter::class, $qB->getAdapter());

        if ($qB->getOptions() instanceof Adapter\AdapterInterface) {
            $options = $qB->getAdapter()->getOptions();
            $this->assertEquals('dummyValue', $options['dummyOption']);
        }

        $this->assertInstanceOf(QueueOptions::class, $qB->getOptions());
        if ($qB->getOptions() instanceof QueueOptions) {
            $this->assertEquals(ZendMessage::class, $qB->getOptions()->getMessageClass());
        }
    }

    public function testCreateServicebyNameWithServiceAdapter()
    {
        $qC = $this->sm->get('queueC');
        $this->assertInstanceOf(Adapter\NullAdapter::class, $qC->getAdapter());
    }

    public function testInvalidServiceNameWillBeIgnored()
    {
        $this->assertFalse($this->sm->has('invalid'));
    }

    public function testCanCreateServiceWithNameAndConfigEmpty()
    {
        $sm = new ServiceManager();
        $sm->setService('Config', null);
        $abstractFactory = new QueueAbstractServiceFactory();
        $this->isFalse($abstractFactory->canCreateServiceWithName($sm, 'foo', 'bar'));
    }

    public function testGetConfigNoKeyConfig()
    {
        $adapter = new ArrayAdapter();
        $adapter->setOptions(['dummyOption' => 'dummyValue']);

        $sm = new ServiceManager();
        $sm->setService('Config', 'foo');
        $abstractFactory = new QueueAbstractServiceFactory();
        $abstractFactory->canCreateServiceWithName($sm, 'foo', 'bar');
    }
}
