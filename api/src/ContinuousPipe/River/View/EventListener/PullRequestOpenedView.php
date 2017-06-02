<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\CodeRepository\Event\PullRequestOpened;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;

class PullRequestOpenedView
{
    private $pullRequestViewStorage;

    public function __construct(PullRequestViewStorage $pullRequestViewStorage)
    {
        $this->pullRequestViewStorage = $pullRequestViewStorage;
    }

    public function notify(PullRequestOpened $event)
    {
        $this->pullRequestViewStorage->add($event->getFlowUuid(), $event->getPullRequest());
    }
}