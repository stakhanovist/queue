<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Adapter;

use Stakhanovist\Queue\Exception;
use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Class AdapterFactory
 */
abstract class AdapterFactory
{
    /**
     * Plugin manager for loading adapters
     *
     * @var null|AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * Instantiate a queue adapter
     *
     * @param  array|Traversable $config
     * @return AdapterInterface
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        if (!isset($config['adapter'])) {
            throw new Exception\InvalidArgumentException('Missing "adapter"');
        }

        if ($config['adapter'] instanceof AdapterInterface) {
            // $config['adapter'] is already an adapter object
            $adapter = $config['adapter'];
        } else {
            $adapter = static::getAdapterPluginManager()->get($config['adapter']);
        }

        if (isset($config['options'])) {
            if (!is_array($config['options'])) {
                throw new Exception\InvalidArgumentException(
                    '
                    "options" must be an array, ' . gettype($config['options']) . ' given.'
                );
            }
            $adapter->setOptions($config['options']);
        }

        return $adapter;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new AdapterPluginManager();
        }
        return static::$adapters;
    }

    /**
     * Change the adapter plugin manager
     *
     * @param  AdapterPluginManager $adapters
     * @return void
     */
    public static function setAdapterPluginManager(AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Resets the internal adapter plugin manager
     *
     * @return void
     */
    public static function resetAdapterPluginManager()
    {
        static::$adapters = null;
    }
}
