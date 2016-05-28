<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\Uuid;

class RunSuccessful implements TideEvent
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

    /**
     * @return Uuid
     */
    public function getRunUuid()
    {
        return $this->deployment->getUuid();
    }
}
