<?php
namespace StakhanovistQueueTest\Parameter;

use Stakhanovist\Queue\Exception\InvalidArgumentException;
use Stakhanovist\Queue\Parameter\ReceiveParameters;

/**
 * Class ReceiveParameterTest
 *
 * @group parameter
 */
class ReceiveParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReceiveParameters
     */
    protected $receiveParameter;

    public function setUp()
    {
        $this->receiveParameter = new ReceiveParameters;
    }

    public function testSetGetClassFilter()
    {
        $this->isNull($this->receiveParameter->getClassFilter());
        $this->assertInstanceOf(ReceiveParameters::class, $this->receiveParameter->setClassFilter('foo'));
        $this->assertSame('foo', $this->receiveParameter->getClassFilter());
    }

    public function testSetClassFilterInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->receiveParameter->setClassFilter([]);
    }

    public function testsetVisibilityTimeoutInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->receiveParameter->setVisibilityTimeout([]);
    }

    public function testSetGetPeekMode()
    {
        $this->isFalse($this->receiveParameter->getPeekMode());
        $this->assertInstanceOf(ReceiveParameters::class, $this->receiveParameter->setPeekMode());
        $this->isTrue($this->receiveParameter->getPeekMode());
    }

    public function testSetPeekInvalidArgument()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $this->receiveParameter->setPeekMode([]);
    }
}
