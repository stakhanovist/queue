<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace StakhanovistQueueTest;

use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Message\Message;
use Stakhanovist\Queue\Message\MessageIterator;
use Stakhanovist\Queue\Queue;
use Stakhanovist\Queue\QueueOptions;

/**
 * Class QueueOptionsTest
 *
 * @group queue
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
        $this->assertSame(Message::class, $this->options->getMessageClass());

        $this->assertInstanceOf(QueueOptions::class, $this->options->setMessageClass('foo'));
        $this->assertSame('foo', $this->options->getMessageClass());
    }

    public function testGetSetMessageSetClass()
    {
        //test default value
        $this->assertSame(MessageIterator::class, $this->options->getMessageSetClass());

        $this->assertInstanceOf(QueueOptions::class, $this->options->setMessageSetClass('foo'));
        $this->assertSame('foo', $this->options->getMessageSetClass());
    }

    public function testGetSetMessageMetadatumKey()
    {
        //test default value
        $this->assertSame('__queue', $this->options->getMessageMetadatumKey());

        $this->assertInstanceOf(QueueOptions::class, $this->options->setMessageMetadatumKey('foo'));
        $this->assertSame('foo', $this->options->getMessageMetadatumKey());
    }

    public function testGetSetEnableAwaitEmulation()
    {
        //test default value
        $this->assertSame(true, $this->options->getEnableAwaitEmulation());

        $this->assertInstanceOf(QueueOptions::class, $this->options->setEnableAwaitEmulation(true));
        $this->assertSame(true, $this->options->getEnableAwaitEmulation());
    }

    public function testGetsetPollingInterval()
    {
        //test default value
        $this->assertSame(1, $this->options->getPollingInterval());

        $this->assertInstanceOf(QueueOptions::class, $this->options->setPollingInterval(10));
        $this->assertSame(10, $this->options->getPollingInterval());
    }
}
