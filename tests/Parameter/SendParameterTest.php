<?php
namespace StakhanovistQueueTest\Parameter;

use Stakhanovist\Queue\Parameter\SendParameters;

class SendParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Stakhanovist\Queue\Parameter\SendParameters
     */
    protected $sendParameter;

    public function setUp()
    {
        $this->sendParameter = new SendParameters();
    }

    public function testSetGetSchedule()
    {
        $this->isFalse($this->sendParameter->getSchedule());
        $this->assertInstanceOf(get_class($this->sendParameter), $this->sendParameter->setSchedule(time()));
        $this->isTrue($this->sendParameter->getSchedule());
    }

    public function testSetScheduleInvalidArgument()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $this->sendParameter->setSchedule('fndfnfbd');
    }

    public function testSetGetRepeatingInterval()
    {
        $this->isFalse($this->sendParameter->getRepeatingInterval());
        $this->assertInstanceOf(get_class($this->sendParameter), $this->sendParameter->setRepeatingInterval(time()));
        $this->isTrue($this->sendParameter->getRepeatingInterval());
    }

    public function testRepeatingIntervalInvalidArgument()
    {
        $this->setExpectedException('Stakhanovist\Queue\Exception\InvalidArgumentException');
        $this->sendParameter->setRepeatingInterval('fndfnfbd');
    }
}
