<?php

namespace ContinuousPipe\River\Task\Deploy\Event;

use ContinuousPipe\Pipe\Client\EnvironmentDeploymentRequest;
use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class DeploymentEvent implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var EnvironmentDeploymentRequest
     */
    private $deploymentRequest;

    /**
     * @param Uuid $tideUuid
     * @param EnvironmentDeploymentRequest $deploymentRequest
     */
    public function __construct(Uuid $tideUuid, EnvironmentDeploymentRequest $deploymentRequest)
    {
        $this->tideUuid = $tideUuid;
        $this->deploymentRequest = $deploymentRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return EnvironmentDeploymentRequest
     */
    public function getDeploymentRequest()
    {
        return $this->deploymentRequest;
    }
}
