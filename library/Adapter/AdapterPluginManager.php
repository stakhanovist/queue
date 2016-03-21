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
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for queue adapters
 *
 * Enforces that adapters retrieved are instances of
 * AdapterInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = [
        'array' => 'Stakhanovist\Queue\Adapter\ArrayAdapter',
        'db' => 'Stakhanovist\Queue\Adapter\Db',
        'mongodb-capped' => 'Stakhanovist\Queue\Adapter\MongoCappedCollection',
        'mongodb' => 'Stakhanovist\Queue\Adapter\MongoCollection',
        'null' => 'Stakhanovist\Queue\Adapter\NullAdapter',
    ];

    /**
     * Do not share by default
     *
     * @var array
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance of AdapterInterface.
     *
     * @param  mixed $plugin
     * @return null
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof AdapterInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\AdapterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
