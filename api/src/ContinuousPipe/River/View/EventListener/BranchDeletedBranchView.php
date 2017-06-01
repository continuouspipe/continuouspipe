<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\CodeRepository\Event\BranchDeleted;
use ContinuousPipe\River\View\Storage\BranchViewStorage;

class BranchDeletedBranchView
{
    private $branchViewStorage;

    public function __construct(BranchViewStorage $branchViewStorage)
    {
        $this->branchViewStorage = $branchViewStorage;
    }

    public function notify(BranchDeleted $event)
    {
        $this->branchViewStorage->save($event->getFlowUuid());
    }
}