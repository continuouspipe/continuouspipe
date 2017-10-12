<?php

namespace ContinuousPipe\River\Task\Run\Event;

use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\River\Event\TideEvent;
use Ramsey\Uuid\UuidInterface;

class RunSuccessful implements TideEvent
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @var Deployment
     */
    private $deployment;

    /**
     * @param UuidInterface $tideUuid
     * @param Deployment    $deployment
     */
    public function __construct(UuidInterface $tideUuid, Deployment $deployment)
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
     * @return UuidInterface
     */
    public function getRunUuid()
    {
        return $this->deployment->getUuid();
    }
}
