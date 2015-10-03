<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\Event\TideEvent;
use GitHub\WebHook\Event\PushEvent;
use Rhumsaa\Uuid\Uuid;

class CodePushed implements TideEvent
{
    /**
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @var PushEvent
     */
    private $gitHubEvent;

    /**
     * @param Uuid      $tideUuid
     * @param PushEvent $gitHubEvent
     */
    public function __construct(Uuid $tideUuid, PushEvent $gitHubEvent)
    {
        $this->tideUuid = $tideUuid;
        $this->gitHubEvent = $gitHubEvent;
    }

    /**
     * {@inheritdoc}
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return PushEvent
     */
    public function getGitHubEvent()
    {
        return $this->gitHubEvent;
    }
}
