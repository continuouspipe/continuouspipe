<?php

namespace ContinuousPipe\Adapter\Kubernetes;

use ContinuousPipe\Adapter\Provider;

class KubernetesProvider implements Provider
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var Cluster
     */
    private $cluster;

    /**
     * @var User
     */
    private $user;

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return Cluster
     */
    public function getCluster()
    {
        return $this->cluster;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterType()
    {
        return KubernetesAdapter::TYPE;
    }
}
