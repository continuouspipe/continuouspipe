<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequest\EnvironmentName;

use ContinuousPipe\Model\Environment;
use ContinuousPipe\River\Task\Deploy\Naming\UnresolvedEnvironmentNameException;
use ContinuousPipe\River\Tide;
use ContinuousPipe\Security\Credentials\Cluster;

interface EnvironmentNamingStrategy
{
    /**
     * Get name of the environment.
     *
     * @param Tide        $tide
     * @param Cluster     $cluster
     * @param string|null $expression
     *
     * @throws UnresolvedEnvironmentNameException
     *
     * @return string
     */
    public function getName(Tide $tide, Cluster $cluster, $expression = null);
}
