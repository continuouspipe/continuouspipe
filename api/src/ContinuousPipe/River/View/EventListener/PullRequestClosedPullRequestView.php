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
        $this->pullRequestViewStorage->deletePullRequest($event->getFlowUuid(), $event->getPullRequest());
    }
}