<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\CodeRepository\Event\CodePushed;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;

class CodePushedPullRequestView
{
    private $pullRequestViewStorage;

    public function __construct(PullRequestViewStorage $pullRequestViewStorage)
    {
        $this->pullRequestViewStorage = $pullRequestViewStorage;
    }

    public function notify(CodePushed $event)
    {
        $this->pullRequestViewStorage->save($event->getFlowUuid(), $event->getCodeReference()->getRepository());
    }
}
