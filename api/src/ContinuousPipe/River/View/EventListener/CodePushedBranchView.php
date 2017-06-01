<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\CodeRepository\Event\CodePushed;
use ContinuousPipe\River\View\Storage\BranchViewStorage;

class CodePushedBranchView
{
    private $branchViewStorage;

    public function __construct(BranchViewStorage $branchViewStorage)
    {
        $this->branchViewStorage = $branchViewStorage;
    }

    public function notify(CodePushed $event)
    {
        $this->branchViewStorage->save($event->getFlowUuid());
    }
}