<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Tide;

interface DeploymentRequestEnhancer
{
    /**
     * @param Tide              $tide
     * @param DeploymentRequest $deploymentRequest
     *
     * @throws DeploymentRequestException
     *
     * @return DeploymentRequest
     */
    public function enhance(Tide $tide, DeploymentRequest $deploymentRequest);
}
