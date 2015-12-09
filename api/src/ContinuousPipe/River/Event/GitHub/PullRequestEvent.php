<?php

namespace ContinuousPipe\River\Event\GitHub;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\Flow;
use GitHub\WebHook\Event\PullRequestEvent as GitHubPullRequestEvent;

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
     * @var Flow
     */
    private $flow;

    /**
     * @param Flow                   $flow
     * @param CodeReference          $codeReference
     * @param GitHubPullRequestEvent $event
     */
    public function __construct(Flow $flow, CodeReference $codeReference, GitHubPullRequestEvent $event)
    {
        $this->flow = $flow;
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
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->codeReference;
    }

    /**
     * @return Flow
     */
    public function getFlow()
    {
        return $this->flow;
    }
}
