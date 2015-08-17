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
     * @param string  $identifier
     * @param Cluster $cluster
     * @param User    $user
     */
    public function __construct($identifier, Cluster $cluster, User $user)
    {
        $this->identifier = $identifier;
        $this->cluster = $cluster;
        $this->user = $user;
    }

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
