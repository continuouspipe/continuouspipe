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
     * @var array|PublicEndpoint[]
     */
    private $publicEndpoints;

    /**
     * @param string $status
     * @param string $clusterIdentifier
     * @param PublicEndpoint[] $publicEndpoints
     */
    public function __construct(string $status, string $clusterIdentifier = null, array $publicEndpoints = [])
    {
        $this->status = $status;
        $this->clusterIdentifier = $clusterIdentifier;
        $this->publicEndpoints = $publicEndpoints;
    }
}
