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
 * Class UnsupportedMethodCallException
 *
 * When unsupported method call, throw this exception
 */
class UnsupportedMethodCallException extends \BadMethodCallException implements
    ExceptionInterface
{
}
