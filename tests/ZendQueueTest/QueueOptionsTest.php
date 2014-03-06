<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueueTest;

use Zend\Config\Config;
use ZendQueue\Adapter;
use ZendQueue\Message\Message;
use ZendQueue\Queue;
use ZendQueue\QueueOptions;
use ZendQueue\Adapter\ArrayAdapter;
use ZendQueue\Message\MessageIterator;
use ZendQueue\Parameter\SendParameters;
use ZendQueue\Adapter\Null;
use ZendQueue\QueueEvent;

/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage UnitTests
 * @group      Zend_Queue
 */
class QueueOptionsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var QueueOptions
     */
    protected $options;

    /**
     * @var Queue
     */
    protected $queue;


    protected function setUp()
    {
        $this->options = new QueueOptions();
    }

    protected function tearDown()
    {

    }

    public function testGetSetMessageClass()
    {
        //test default value
        $this->assertSame('\ZendQueue\Message\Message', $this->options->getMessageClass());

        $this->assertInstanceOf('ZendQueue\QueueOptions', $this->options->setMessageClass('foo'));
        $this->assertSame('foo', $this->options->getMessageClass());
    }

    public function testGetSetMessageSetClass()
    {
        //test default value
        $this->assertSame('\ZendQueue\Message\MessageIterator', $this->options->getMessageSetClass());

        $this->assertInstanceOf('ZendQueue\QueueOptions', $this->options->setMessageSetClass('foo'));
        $this->assertSame('foo', $this->options->getMessageSetClass());
    }

    public function testGetSetMessageMetadatumKey()
    {
        //test default value
        $this->assertSame('__queue', $this->options->getMessageMetadatumKey());

        $this->assertInstanceOf('ZendQueue\QueueOptions', $this->options->setMessageMetadatumKey('foo'));
        $this->assertSame('foo', $this->options->getMessageMetadatumKey());
    }

    public function testGetSetEnableAwaitEmulation()
    {
        //test default value
        $this->assertSame(true, $this->options->getEnableAwaitEmulation());

        $this->assertInstanceOf('ZendQueue\QueueOptions', $this->options->setEnableAwaitEmulation(true));
        $this->assertSame(true, $this->options->getEnableAwaitEmulation());
    }

    public function testGetsetPollingInterval()
    {
        //test default value
        $this->assertSame(1, $this->options->getPollingInterval());

        $this->assertInstanceOf('ZendQueue\QueueOptions', $this->options->setPollingInterval(10));
        $this->assertSame(10, $this->options->getPollingInterval());
    }

}
