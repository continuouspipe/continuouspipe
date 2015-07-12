<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\AdapterRegistry;
use ContinuousPipe\Adapter\Provider;

class ProviderRepository
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
     * @return Provider[]
     */
    public function findAll()
    {
        $providers = [];

        foreach ($this->adapterRegistry->getAdapters() as $adapter) {
            foreach ($adapter->getRepository()->findAll() as $provider) {
                $providerName = $adapter->getType().'/'.$provider->getIdentifier();

                $providers[$providerName] = $provider;
            }
        }

        return $providers;
    }

    /**
     * @param string $providerName
     *
     * @return Provider
     */
    public function findOneByName($providerName)
    {
        list($adapter, $identifier) = explode('/', $providerName);

        return $this->adapterRegistry->getByType($adapter)->getRepository()->find($identifier);
    }
}
