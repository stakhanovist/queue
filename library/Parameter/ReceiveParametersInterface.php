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

interface ReceiveParametersInterface
{

    const CLASS_FILTER = 'classFilter';
    const VISIBILITY_TIMEOUT = 'visibilityTimeout';
    const PEEK_MODE = 'peekMode';

    /**
     * Get the class filter
     *
     * @see setVisibilityTimeout()
     *
     * @return string|null
     */
    public function getClassFilter();

    /**
     * Get the visibility timeout
     *
     * Visibility timeout is how long a message is left in the queue
     * "invisible" to other readers.  If the message is acknowledged (deleted)
     * before the timeout, then the message is deleted.  However, if the
     * timeout expires then the message will be made available to other queue
     * readers.
     *
     * @return int|null
     */
    public function getVisibilityTimeout();

    /**
     * Get peek mode
     *
     * If true, peek at the messages from the specified queue without removing them.
     *
     * @throws Exception\InvalidArgumentException
     * @return boolean
     */
    public function getPeekMode();

    /**
     * @return array
     */
    public function toArray();

}
