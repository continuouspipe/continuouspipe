<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Adapter\AdapterNotFound;
use ContinuousPipe\Adapter\AdapterRegistry;
use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Adapter\ProviderNotFound;

class AdapterProviderRepository
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
     * Find all providers of all adapters.
     *
     * @return Provider[]
     */
    public function findAll()
    {
        $providers = [];

        foreach ($this->adapterRegistry->getAdapters() as $adapter) {
            foreach ($adapter->getRepository()->findAll() as $provider) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Find a provider by its type and its identifier.
     *
     * @param string $type
     * @param string $identifier
     *
     * @return Provider
     *
     * @throws ProviderNotFound
     */
    public function findByTypeAndIdentifier($type, $identifier)
    {
        try {
            return $this->adapterRegistry->getByType($type)->getRepository()->find($identifier);
        } catch (AdapterNotFound $e) {
            throw new ProviderNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Create a new provider.
     *
     * @param Provider $provider
     *
     * @return Provider
     */
    public function create(Provider $provider)
    {
        return $this->getRepository($provider->getAdapterType())->create($provider);
    }

    /**
     * @param Provider $provider
     */
    public function remove(Provider $provider)
    {
        $this->getRepository($provider->getAdapterType())->remove($provider);
    }

    /**
     * @param string $type
     *
     * @return \ContinuousPipe\Adapter\ProviderRepository
     *
     * @throws \ContinuousPipe\Adapter\AdapterNotFound
     */
    private function getRepository($type)
    {
        return $this->adapterRegistry->getByType($type)->getRepository();
    }
}
