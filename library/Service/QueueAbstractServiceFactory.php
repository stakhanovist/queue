<?php
/**
 * Stakhanovist
 *
 * @link        https://github.com/stakhanovist/queue
 * @copyright   Copyright (c) 2015, Stakhanovist
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Stakhanovist\Queue\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Stakhanovist\Queue\Adapter;
use Stakhanovist\Queue\Queue;

/**
 * Queue factory for multiple queues.
 */
class QueueAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key root node
     *
     * @var string
     */
    protected $configKeyRoot = 'stakhanovist';

    /**
     * Configuration key for queues objects
     *
     * @var string
     */
    protected $configKey = 'queues';


    /**
     * Can we create a queue by the requested name?
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        if (empty($config)) {
            return false;
        }

        return (
            isset($config[$requestedName])
            && is_array($config[$requestedName])
            && !empty($config[$requestedName])
            && isset($config[$requestedName]['name'])
            && isset($config[$requestedName]['adapter'])
        );
    }

    /**
     * Create a queue
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return Queue
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);

        $config = $config[$requestedName];

        if (isset($config['adapter']) && is_string($config['adapter']) && $services->has($config['adapter'])) {
            $adapter = $services->get($config['adapter']);
            if ($adapter instanceof Adapter\AdapterInterface) {
                $config['adapter'] = $adapter;
            }
        }

        $queue = Queue::factory($config);
        return $queue;
    }

    /**
     * Get queues configuration, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $services->get('Config');

        if (!isset($config[$this->configKeyRoot])
            || !is_array($config[$this->configKeyRoot])
        ) {
            $this->config = [];
            return $this->config;
        }

        $config = $config[$this->configKeyRoot];

        if (!isset($config[$this->configKey])
            || !is_array($config[$this->configKey])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}
