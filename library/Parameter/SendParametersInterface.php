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

/**
 * Interface SendParametersInterface
 */
interface SendParametersInterface
{

    const SCHEDULE = 'schedule';
    const REPEATING_INTERVAL = 'repeatingInterval';

    /**
     * Get message schedule time
     *
     * If schedule has been set, the message will be "invisibile" to readers
     * until the scheduled time.
     *
     * @return int|null
     */
    public function getSchedule();

    /**
     * Get message repeating interval
     *
     * If repeating interval has been set, each time the message is acknowledged by a reader
     * the message will be auto scheduled at current time plus interval seconds
     *
     * @return int|boolean
     */
    public function getRepeatingInterval();

    /**
     * @return array
     */
    public function toArray();
}
