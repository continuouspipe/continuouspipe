<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\Adapter;
use ContinuousPipe\Adapter\EnvironmentClientFactory;
use ContinuousPipe\Adapter\ProviderRepository;

class KubernetesAdapter implements Adapter
{
    const TYPE = 'kubernetes';

    /**
     * @var ProviderRepository
     */
    private $repository;

    /**
     * @var KubernetesEnvironmentClientFactory
     */
    private $kubernetesEnvironmentClientFactory;

    public function __construct(ProviderRepository $repository, KubernetesEnvironmentClientFactory $kubernetesEnvironmentClientFactory)
    {
        $this->repository = $repository;
        $this->kubernetesEnvironmentClientFactory = $kubernetesEnvironmentClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationClass()
    {
        return KubernetesProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return EnvironmentClientFactory
     */
    public function getEnvironmentClientFactory()
    {
        return $this->kubernetesEnvironmentClientFactory;
    }
}
