<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Flow\Event\BranchPinned;
use ContinuousPipe\River\View\Storage\BranchViewStorage;

class BranchPinnedView
{
    private $branchViewStorage;

    public function __construct(BranchViewStorage $branchViewStorage)
    {
        $this->branchViewStorage = $branchViewStorage;
    }

    public function notify(BranchPinned $event)
    {
        $this->branchViewStorage->branchPinned($event->getFlowUuid(), $event->getBranch());
    }
}