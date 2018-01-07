<?php

namespace ContinuousPipe\Pipe\Client\DeploymentRequest;

/**
 * @deprecated Duplicate of the `ContinuousPipe\Pipe\DeploymentRequest\Target` object, after merging pipe.
 *             Kept to be compatible with serialized tides.
 */
class Target extends \ContinuousPipe\Pipe\DeploymentRequest\Target
{
    private $environmentName;
    private $clusterIdentifier;
    private $environmentLabels;

    public function getEnvironmentName()
    {
        return $this->environmentName ?? parent::getEnvironmentName();
    }

    public function getClusterIdentifier()
    {
        return $this->clusterIdentifier ?? parent::getClusterIdentifier();
    }

    public function getEnvironmentLabels()
    {
        return $this->environmentLabels ?? parent::getEnvironmentLabels();
    }
}
