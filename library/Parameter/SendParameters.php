<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Parameter;

use Stakhanovist\Queue\Exception;
use Zend\Stdlib\Parameters;

/**
 * Class SendParameters
 */
class SendParameters extends Parameters implements SendParametersInterface
{
    /**
     * Set message schedule time
     *
     * $scheduleTime must be an unix timestamp or null (to disable this feature)
     * If $scheduleTime is setted, the message will be "invisibile" to readers
     * until the scheduled time.
     *
     * @param int $scheduleTime
     * @throws Exception\InvalidArgumentException
     * @return $this
     */
    public function setSchedule($scheduleTime = null)
    {
        if (($scheduleTime !== null) && !is_int($scheduleTime)) {
            throw new Exception\InvalidArgumentException('$scheduleTime must be a int or null');
        }
        $this->set(self::SCHEDULE, $scheduleTime);
        return $this;
    }

    /**
     * Get message schedule time
     *
     * @see setSchedule()
     *
     * @return int|null
     */
    public function getSchedule()
    {
        return $this->get(self::SCHEDULE, false);
    }

    /**
     * Set message repeating interval
     *
     * $repeatingInterval must be an int (seconds) or null (to disable this feature)
     * If $repeatingInterval is setted, each time the message is acknowledged by a reader
     * the message will be auto scheduled at current time plus interval seconds
     *
     * @param int $repeatingInterval
     * @throws Exception\InvalidArgumentException
     * @return $this
     */
    public function setRepeatingInterval($repeatingInterval = null)
    {
        if (($repeatingInterval !== null) && !is_int($repeatingInterval)) {
            throw new Exception\InvalidArgumentException('$repeatingInterval must be a int or null');
        }
        $this->set(self::REPEATING_INTERVAL, $repeatingInterval);
        return $this;
    }

    /**
     * Get message repeating interval
     *
     * @see setRepeatingInterval()
     *
     * @return int|boolean
     */
    public function getRepeatingInterval()
    {
        return $this->get(self::REPEATING_INTERVAL, false);
    }
}
