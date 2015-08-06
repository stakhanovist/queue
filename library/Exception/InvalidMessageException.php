<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Exception;

/**
 * Class InvalidMessageException
 *
 * Exception for Queue component.
 */
class InvalidMessageException extends \InvalidArgumentException implements
    ExceptionInterface
{
}
