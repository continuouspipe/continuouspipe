<?php

namespace ContinuousPipe\Security\Credentials;

use ContinuousPipe\Security\Credentials\Cluster\ClusterPolicy;

abstract class Cluster
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var ClusterPolicy[]
     */
    private $policies;

    /**
     * @param string $identifier
     * @param array $policies
     */
    public function __construct($identifier, array $policies = [])
    {
        $this->identifier = $identifier;
        $this->policies = $policies;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return ClusterPolicy[]
     */
    public function getPolicies(): array
    {
        return $this->policies ?: [];
    }

    /**
     * @param ClusterPolicy[] $policies
     *
     * @return Cluster
     */
    public function withPolicies(array $policies) : self
    {
        $cluster = clone $this;
        $cluster->policies = $policies;

        return $cluster;
    }
}
