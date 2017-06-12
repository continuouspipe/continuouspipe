<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\CodeRepository\Event\BranchDeleted;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;

class BranchDeletedPullRequestView
{
    private $pullRequestViewStorage;

    public function __construct(PullRequestViewStorage $pullRequestViewStorage)
    {
        $this->pullRequestViewStorage = $pullRequestViewStorage;
    }

    public function notify(BranchDeleted $event)
    {
        $this->pullRequestViewStorage->deleteBranch($event->getFlowUuid(), $event->getCodeReference()->getBranch());
    }
}