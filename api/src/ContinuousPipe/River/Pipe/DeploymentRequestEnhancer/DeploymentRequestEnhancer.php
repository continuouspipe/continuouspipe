<?php

namespace ContinuousPipe\River\Pipe\DeploymentRequestEnhancer;

use ContinuousPipe\Pipe\DeploymentRequest;
use ContinuousPipe\River\Pipe\DeploymentRequest\DeploymentRequestException;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;

interface DeploymentRequestEnhancer
{
    /**
     * @param Tide              $tide
     * @param DeploymentRequest $deploymentRequest
     * @param TaskDetails       $taskDetails
     *
     * @throws DeploymentRequestException
     *
     * @return DeploymentRequest
     */
    public function enhance(Tide $tide, TaskDetails $taskDetails, DeploymentRequest $deploymentRequest);
}
