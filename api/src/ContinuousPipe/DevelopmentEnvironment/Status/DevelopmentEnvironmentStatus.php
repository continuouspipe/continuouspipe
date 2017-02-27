<?php

namespace ContinuousPipe\DevelopmentEnvironment\Status;

use ContinuousPipe\Pipe\Client\PublicEndpoint;

class DevelopmentEnvironmentStatus
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $clusterIdentifier;

    /**
     * @var string
     */
    private $environmentName;

    /**
     * @var array|PublicEndpoint[]
     */
    private $publicEndpoints = [];

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    /**
     * @return array|PublicEndpoint[]
     */
    public function getPublicEndpoints()
    {
        return $this->publicEndpoints;
    }

    public function withCluster(string $cluster) : self
    {
        $status = clone $this;
        $status->clusterIdentifier = $cluster;

        return $status;
    }

    public function withPublicEndpoints(array $endpoints) : self
    {
        $status = clone $this;
        $status->publicEndpoints = $endpoints;

        return $status;
    }

    public function withEnvironmentName(string $environmentName) : self
    {
        $status = clone $this;
        $status->environmentName = $environmentName;

        return $status;
    }

    public function withStatus(string $environmentStatus) : self
    {
        $status = clone $this;
        $status->status = $environmentStatus;

        return $status;
    }
}
