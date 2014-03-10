<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace ZendQueue\Adapter;

use Traversable;
use ZendQueue\Exception;
use Zend\Stdlib\ArrayUtils;


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
                throw new Exception\InvalidArgumentException('
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
