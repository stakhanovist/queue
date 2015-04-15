<?php
namespace StakhanovistQueueTest\Parameter;

use Stakhanovist\Queue\Parameter\ReceiveParameters;

class ReceiveParameterTest extends \PHPUnit_Framework_TestCase
{

    protected $receiveParameter;

    public function setUp()
    {
        $this->receiveParameter = new ReceiveParameters();
    }

    public function testSetGetClassFilter()
    {
        $this->isNull($this->receiveParameter->getClassFilter());
        $this->assertInstanceOf('Stakhanovist\Queue\Parameter\ReceiveParameters', $this->receiveParameter->setClassFilter('foo'));
        $this->assertSame('foo', $this->receiveParameter->getClassFilter());
    }

    public function testSetClassFilterInvalidArgument()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $this->receiveParameter->setClassFilter(array());
    }

    public function testsetVisibilityTimeoutInvalidArgument()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $this->receiveParameter->setVisibilityTimeout(array());
    }

    public function testSetGetPeekMode()
    {
        $this->isFalse($this->receiveParameter->getPeekMode());
        $this->assertInstanceOf('Stakhanovist\Queue\Parameter\ReceiveParameters', $this->receiveParameter->setPeekMode());
        $this->isTrue($this->receiveParameter->getPeekMode());
    }

    public function testSetPeekInvalidArgument()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $this->receiveParameter->setPeekMode(array());
    }
}
