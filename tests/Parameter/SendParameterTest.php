<?php
namespace StakhanovistQueueTest\Parameter;

use Stakhanovist\Queue\Exception\InvalidArgumentException;
use Stakhanovist\Queue\Parameter\SendParameters;

/**
 * Class SendParameterTest
 *
 * @group parameter
 */
class SendParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SendParameters
     */
    protected $sendParameter;

    public function setUp()
    {
        $this->sendParameter = new SendParameters;
    }

    public function testSetGetSchedule()
    {
        $this->isFalse($this->sendParameter->getSchedule());
        $this->assertInstanceOf(get_class($this->sendParameter), $this->sendParameter->setSchedule(time()));
        $this->isTrue($this->sendParameter->getSchedule());
    }

    public function testSetScheduleInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
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
        $this->setExpectedException(InvalidArgumentException::class);
        $this->sendParameter->setRepeatingInterval('fndfnfbd');
    }
}
