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
use Stakhanovist\Queue\Adapter\AdapterFactory;

/**
 * Queue adapter factory for multiple adapters.
 */
class QueueAdapterAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key for adapters objects
     *
     * @var string
     */
    protected $configKey = 'queue_adapters';

    /**
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

        return (isset($config[$requestedName]) && is_array($config[$requestedName]));
    }

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string $name
     * @param  string $requestedName
     * @return \Stakhanovist\Queue\Adapter\AdapterInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        $config = $config[$requestedName];
        return AdapterFactory::factory($config);
    }

    /**
     * Retrieve queues configuration, if any
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
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}
