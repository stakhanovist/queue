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
use Stakhanovist\Queue\QueueInterface;

/**
 * Interface CountMessagesCapableInterface
 */
interface CountMessagesCapableInterface extends AdapterInterface
{
    /**
     * Returns the approximate number of messages in the queue
     *
     * @param QueueInterface $queue
     * @return integer|null
     */
    public function countMessages(QueueInterface $queue);
}
