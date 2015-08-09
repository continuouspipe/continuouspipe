<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\AdapterRegistry;
use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Adapter\ProviderRepository;

class AdapterProviderRepository implements ProviderRepository
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        list($adapter, $identifier) = explode('/', $identifier);

        return $this->adapterRegistry->getByType($adapter)->getRepository()->find($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Provider $provider)
    {
        return $this->adapterRegistry->getByType($provider->getAdapterType())->getRepository()->create($provider);
    }
}
