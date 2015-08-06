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
use Stakhanovist\Queue\QueueOptions;
use Zend\ServiceManager\ServiceManager;

/**
 *
 * @group  Stakhanovist_Queue
 */
class QueueAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    public function setUp()
    {
        $adapter = new ArrayAdapter();
        $adapter->setOptions(['dummyOption' => 'dummyValue']);

        $this->sm = new ServiceManager();
        $this->sm->setInvokableClass('custom', 'Stakhanovist\Queue\Adapter\Null');
        $this->sm->setService('Config', array(
            'stakhanovist' => array(
                'queues' => array(
                    'queueA' => array(
                        'name' => 'A',
                        'adapter' => array( //Adapter as config
                            'adapter' => 'ArrayAdapter',
                            'options' => array('dummyOption' => 'dummyValue'),
                        ),
                        'options' => array('messageClass' => 'Zend\Stdlib\Message'),
                    ),


                    'queueB' => array(
                        'name' => 'B',
                        'adapter' => $adapter, //Adapter as instance
                        'options' => array('messageClass' => 'Zend\Stdlib\Message'),
                    ),

                    'queueC' => array(
                        'name' => 'C',
                        'adapter' => 'custom',
                        'options' => array('messageClass' => 'Zend\Stdlib\Message'),
                    ),
            ))));
        $this->sm->addAbstractFactory('Stakhanovist\Queue\Service\QueueAbstractServiceFactory');
    }

    public function testCanLookupQueueByName()
    {
        $this->assertTrue($this->sm->has('queueA'));
        $this->assertTrue($this->sm->has('queueB'));
    }

    public function testCanRetrieveQueueByName()
    {
        $qA = $this->sm->get('queueA');
        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $qA);

        $qB = $this->sm->get('queueB');
        $this->assertInstanceOf('Stakhanovist\Queue\Queue', $qB);

        $this->assertNotSame($qA, $qB);
    }

    public function testConfiguration()
    {
        $qA = $this->sm->get('queueA');
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\ArrayAdapter', $qA->getAdapter());

        if ($qA->getOptions() instanceof Adapter\AdapterInterface) {
            $options = $qA->getAdapter()->getOptions();
            $this->assertEquals('dummyValue', $options['dummyOption']);
        }

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $qA->getOptions());
        if ($qA->getOptions() instanceof QueueOptions) {
            $this->assertEquals('Zend\Stdlib\Message', $qA->getOptions()->getMessageClass());
        }


        $qB = $this->sm->get('queueB');
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\ArrayAdapter', $qB->getAdapter());

        if ($qB->getOptions() instanceof Adapter\AdapterInterface) {
            $options = $qB->getAdapter()->getOptions();
            $this->assertEquals('dummyValue', $options['dummyOption']);
        }

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $qB->getOptions());
        if ($qB->getOptions() instanceof QueueOptions) {
            $this->assertEquals('Zend\Stdlib\Message', $qB->getOptions()->getMessageClass());
        }
    }

    public function testCreateServicebyNameWithServiceAdapter()
    {
        $qC = $this->sm->get('queueC');
        $this->assertInstanceOf('Stakhanovist\Queue\Adapter\Null', $qC->getAdapter());
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
