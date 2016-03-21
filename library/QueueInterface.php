<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue;

/**
 * Interface QueueInterface
 */
interface QueueInterface
{
    /**
     * Get the queue name
     *
     * @return string
     */
    public function getName();

    /**
     * Get options
     *
     * @return QueueOptions
     */
    public function getOptions();
}
