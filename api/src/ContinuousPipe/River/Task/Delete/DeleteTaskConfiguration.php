<?php

namespace ContinuousPipe\River\Task\Delete;

use ContinuousPipe\River\Pipe\EnvironmentAwareConfiguration;

class DeleteTaskConfiguration implements EnvironmentAwareConfiguration
{
    /**
     * @var string|null
     */
    private $clusterIdentifier;

    /**
     * @var string|null
     */
    private $environmentName;

    public function __construct(string $clusterIdentifier = null, string $environmentName = null)
    {
        $this->clusterIdentifier = $clusterIdentifier;
        $this->environmentName = $environmentName;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentName()
    {
        return $this->environmentName;
    }

    /**
     * {@inheritdoc}
     */
    public function getClusterIdentifier()
    {
        return $this->clusterIdentifier;
    }
}
