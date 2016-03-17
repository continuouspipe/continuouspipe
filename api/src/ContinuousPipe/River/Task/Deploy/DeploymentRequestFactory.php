<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Task\Deploy\Naming\UnresolvedEnvironmentNameException;

interface DeploymentRequestFactory
{
    /**
     * Create a deployment request for the pipe client based on that pipe.
     *
     * @param DeployContext           $context
     * @param DeployTaskConfiguration $configuration
     *
     * @throws UnresolvedEnvironmentNameException
     *
     * @return DeploymentRequest
     */
    public function create(DeployContext $context, DeployTaskConfiguration $configuration);
}
