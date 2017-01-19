<?php

namespace ContinuousPipe\Pipe\Event;

use Ramsey\Uuid\Uuid;

class DeploymentSuccessful implements DeploymentEvent
{
    /**
     * @var Uuid
     */
    private $deploymentUuid;

    /**
     * @param Uuid $deploymentUuid
     */
    public function __construct(Uuid $deploymentUuid)
    {
        $this->deploymentUuid = $deploymentUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeploymentUuid()
    {
        return $this->deploymentUuid;
    }
}
