<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use GitHub\WebHook\Event\PullRequestEvent as GitHubPullRequestEvent;
use Ramsey\Uuid\UuidInterface;

abstract class PullRequestEvent implements CodeRepositoryEvent
{
    /**
     * @var GitHubPullRequestEvent
     */
    private $event;

    /**
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @param UuidInterface          $flowUuid
     * @param CodeReference          $codeReference
     * @param GitHubPullRequestEvent $event
     */
    public function __construct(UuidInterface $flowUuid, CodeReference $codeReference, GitHubPullRequestEvent $event)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
        $this->event = $event;
    }

    /**
     * @return GitHubPullRequestEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }
}
