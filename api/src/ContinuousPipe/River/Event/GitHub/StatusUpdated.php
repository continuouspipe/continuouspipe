<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\Event\TideEvent;
use GitHub\WebHook\Event\StatusEvent;
use Ramsey\Uuid\Uuid;

class StatusUpdated implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var StatusEvent
     */
    private $gitHubStatusEvent;

    /**
     * @param Uuid        $tideUuid
     * @param StatusEvent $gitHubStatusEvent
     */
    public function __construct(Uuid $tideUuid, StatusEvent $gitHubStatusEvent)
    {
        $this->tideUuid = $tideUuid;
        $this->gitHubStatusEvent = $gitHubStatusEvent;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return StatusEvent
     */
    public function getGitHubStatusEvent()
    {
        return $this->gitHubStatusEvent;
    }
}
