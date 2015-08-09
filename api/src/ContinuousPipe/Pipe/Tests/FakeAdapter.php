<?php

namespace ContinuousPipe\Pipe\Tests;

use ContinuousPipe\Adapter\Adapter;
use ContinuousPipe\Adapter\ProviderRepository;

class FakeAdapter implements Adapter
{
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @param ProviderRepository $providerRepository
     */
    public function __construct(ProviderRepository $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'fake';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationClass()
    {
        return FakeProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->providerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentClientFactory()
    {
        throw new \RuntimeException('Not implemented in this fake adapter right now');
    }
}
