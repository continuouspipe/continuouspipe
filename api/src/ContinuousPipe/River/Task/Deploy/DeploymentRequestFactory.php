<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Pipe\Client\DeploymentRequest;
use ContinuousPipe\River\Task\TaskDetails;
use ContinuousPipe\River\Tide;

interface DeploymentRequestFactory
{
    /**
     * Create a deployment request for the pipe client based on that pipe.
     *
     * @param Tide                    $tide
     * @param TaskDetails             $taskDetails
     * @param DeployTaskConfiguration $configuration
     *
     * @return DeploymentRequest
     */
    public function create(Tide $tide, TaskDetails $taskDetails, DeployTaskConfiguration $configuration);
}
