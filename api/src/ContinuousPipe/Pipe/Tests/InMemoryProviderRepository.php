<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Adapter\Provider;
use ContinuousPipe\Adapter\ProviderNotFound;
use ContinuousPipe\Adapter\ProviderRepository;

class InMemoryProviderRepository implements ProviderRepository
{
    /**
     * @var Provider[]
     */
    private $providers = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return array_values($this->providers);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Provider $provider)
    {
        $this->providers[$provider->getIdentifier()] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function find($identifier)
    {
        if (!array_key_exists($identifier, $this->providers)) {
            throw new ProviderNotFound();
        }

        return $this->providers[$identifier];
    }
}
