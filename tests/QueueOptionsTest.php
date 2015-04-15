<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest;

use Zend\Config\Config;
use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Message\Message;
use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\QueueOptions;
use Stakhanovist\Queue\Adapter\ArrayAdapter;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Parameter\SendParameters;
use Stakhanovist\Queue\Adapter\Null;
use Stakhanovist\Queue\QueueEvent;

/**
 *
 * @group      Stakhanovist_Queue
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
        $this->assertSame('\Stakhanovist\Queue\Message\Message', $this->options->getMessageClass());

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $this->options->setMessageClass('foo'));
        $this->assertSame('foo', $this->options->getMessageClass());
    }

    public function testGetSetMessageSetClass()
    {
        //test default value
        $this->assertSame('\Stakhanovist\Queue\Message\MessageIterator', $this->options->getMessageSetClass());

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $this->options->setMessageSetClass('foo'));
        $this->assertSame('foo', $this->options->getMessageSetClass());
    }

    public function testGetSetMessageMetadatumKey()
    {
        //test default value
        $this->assertSame('__queue', $this->options->getMessageMetadatumKey());

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $this->options->setMessageMetadatumKey('foo'));
        $this->assertSame('foo', $this->options->getMessageMetadatumKey());
    }

    public function testGetSetEnableAwaitEmulation()
    {
        //test default value
        $this->assertSame(true, $this->options->getEnableAwaitEmulation());

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $this->options->setEnableAwaitEmulation(true));
        $this->assertSame(true, $this->options->getEnableAwaitEmulation());
    }

    public function testGetsetPollingInterval()
    {
        //test default value
        $this->assertSame(1, $this->options->getPollingInterval());

        $this->assertInstanceOf('Stakhanovist\Queue\QueueOptions', $this->options->setPollingInterval(10));
        $this->assertSame(10, $this->options->getPollingInterval());
    }

}
