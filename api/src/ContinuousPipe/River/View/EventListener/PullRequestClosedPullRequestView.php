<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;

class PullRequestClosedPullRequestView
{
    private $pullRequestViewStorage;

    public function __construct(PullRequestViewStorage $pullRequestViewStorage)
    {
        $this->pullRequestViewStorage = $pullRequestViewStorage;
    }

    public function notify(PullRequestClosed $event)
    {
        if (null !== $pullRequst = $event->getPullRequest()) {
            $this->pullRequestViewStorage->deletePullRequest($event->getFlowUuid(), $pullRequst);
        }
    }
}