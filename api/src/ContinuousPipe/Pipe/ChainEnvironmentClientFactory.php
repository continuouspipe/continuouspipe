<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\AdapterRegistry;
use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\Provider;

class ChainEnvironmentClientFactory implements EnvironmentClientFactory
{
    /**
     * @var AdapterRegistry
     */
    private $adapterRegistry;

    /**
     * @param AdapterRegistry $adapterRegistry
     */
    public function __construct(AdapterRegistry $adapterRegistry)
    {
        $this->adapterRegistry = $adapterRegistry;
    }

    /**
     * @param Provider $provider
     *
     * @return EnvironmentClientFactory
     */
    public function getByProvider(Provider $provider)
    {
        $adapter = $this->adapterRegistry->getByType($provider->getAdapterType());

        return $adapter->getEnvironmentClientFactory()->getByProvider($provider);
    }
}
