<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Pipe\Client\DeploymentRequest;

interface DeploymentRequestFactory
{
    /**
     * Create a deployment request for the pipe client based on that pipe.
     *
     * @param DeployContext $context
     *
     * @return DeploymentRequest
     */
    public function create(DeployContext $context);
}
