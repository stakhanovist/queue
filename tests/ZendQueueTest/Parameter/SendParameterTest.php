<?php
namespace ZendQueueTest\Parameter;

use ZendQueue\Parameter\SendParameters;

class SendParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ZendQueue\Parameter\SendParameters
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
        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');
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
        $this->setExpectedException('ZendQueue\Exception\InvalidArgumentException');
        $this->sendParameter->setRepeatingInterval('fndfnfbd');
    }

}
