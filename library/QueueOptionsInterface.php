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
 * Interface QueueOptionsInterface
 */
interface QueueOptionsInterface
{
    /**
     * @return string
     */
    public function getMessageClass();

    /**
     * @return string
     */
    public function getMessageSetClass();

    /**
     * Get the key name used to embed queue info into message's metadata
     *
     * @return string
     */
    public function getMessageMetadatumKey();

    /**
     * @return boolean
     */
    public function getEnableAwaitEmulation();

    /**
     * @return int
     */
    public function getPollingInterval();
}
