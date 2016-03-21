<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Adapter\Capabilities;

use Stakhanovist\Queue\Adapter\AdapterInterface;

/**
 * Interface ListQueuesCapableInterface
 */
interface ListQueuesCapableInterface extends AdapterInterface
{
    /**
     * Get an array of all available queues
     *
     * @return array
     */
    public function listQueues();
}
