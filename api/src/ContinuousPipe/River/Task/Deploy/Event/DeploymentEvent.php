<?php

namespace ContinuousPipe\River\Task\Deploy\Event;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Event\TideEvent;
use Rhumsaa\Uuid\Uuid;

class DeploymentEvent implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var Deployment
     */
    private $deployment;

    /**
     * @param Uuid       $tideUuid
     * @param Deployment $deployment
     */
    public function __construct(Uuid $tideUuid, Deployment $deployment)
    {
        $this->tideUuid = $tideUuid;
        $this->deployment = $deployment;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return Deployment
     */
    public function getDeployment()
    {
        return $this->deployment;
    }
}
