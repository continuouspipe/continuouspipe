<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\Adapter;
use ContinuousPipe\Adapter\EnvironmentClientFactory;

class KubernetesAdapter implements Adapter
{
    const TYPE = 'kubernetes';

    /**
     * @var KubernetesEnvironmentClientFactory
     */
    private $kubernetesEnvironmentClientFactory;

    public function __construct(KubernetesEnvironmentClientFactory $kubernetesEnvironmentClientFactory)
    {
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
     * @return EnvironmentClientFactory
     */
    public function getEnvironmentClientFactory()
    {
        return $this->kubernetesEnvironmentClientFactory;
    }
}
