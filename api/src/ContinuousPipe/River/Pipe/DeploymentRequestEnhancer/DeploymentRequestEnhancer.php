<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\View\Tide;

interface DeploymentRequestEnhancer
{
    /**
     * @param Tide              $tide
     * @param DeploymentRequest $deploymentRequest
     *
     * @return DeploymentRequest
     */
    public function enhance(Tide $tide, DeploymentRequest $deploymentRequest);
}
