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

class ReceiveParameters extends Parameters
{

    const CLASS_FILTER = 'classFilter';
    const VISIBILITY_TIMEOUT = 'visibilityTimeout';
    const PEEK_MODE = 'peekMode';

    /**
     * Set the class filter
     *
     * Filter (receive) only message of the given class name.
     *
     * @param string $classname
     * @return \Stakhanovist\Queue\Parameter\ReceiveParameters
     */
    public function setClassFilter($classname = null)
    {
        if (($classname !== null) && !is_string($classname)) {
            throw new Exception\InvalidArgumentException('$classname must be a string or null');
        }

        //FIXME: temporary workaround to avoid absolute FQCN
        $classname = ltrim($classname, '\\');

        $this->set(self::CLASS_FILTER, $classname);
        return $this;
    }

    /**
     * Get the class filter
     *
     * @see setVisibilityTimeout()
     *
     * @return string|null
     */
    public function getClassFilter()
    {
        return $this->get(self::CLASS_FILTER, null);
    }

    /**
     * Set the visibility timeout
     *
     * Visibility timeout is how long a message is left in the queue
     * "invisible" to other readers.  If the message is acknowledged (deleted)
     * before the timeout, then the message is deleted.  However, if the
     * timeout expires then the message will be made available to other queue
     * readers.
     *
     * @param int $timeout
     * @throws Exception\InvalidArgumentException
     * @return \Stakhanovist\Queue\Parameter\ReceiveParameters
     */
    public function setVisibilityTimeout($timeout = null)
    {
        if (($timeout !== null) && !is_integer($timeout)) {
            throw new Exception\InvalidArgumentException('$timeout must be an integer or null');
        }

        $this->set(self::VISIBILITY_TIMEOUT, $timeout);
        return $this;
    }

    /**
     * Get the visibility timeout
     *
     * @see setVisibilityTimeout()
     *
     * @return int|null
     */
    public function getVisibilityTimeout()
    {
        return $this->get(self::VISIBILITY_TIMEOUT, null);
    }

    /**
     * Set peek mode
     *
     * If true, peek at the messages from the specified queue without removing them.
     *
     * @param bool $peek
     * @throws Exception\InvalidArgumentException
     * @return \Stakhanovist\Queue\Parameter\ReceiveParameters
     */
    public function setPeekMode($peek = true)
    {
        if (!is_bool($peek)) {
            throw new Exception\InvalidArgumentException('$peek must be a boolean');
        }

        $this->set(self::PEEK_MODE, $peek);
        return $this;
    }

    /**
     * Get peek mode
     *
     * @see setPeekMode()
     *
     * @throws Exception\InvalidArgumentException
     * @return boolean
     */
    public function getPeekMode()
    {
        return $this->get(self::PEEK_MODE, false);
    }


}
