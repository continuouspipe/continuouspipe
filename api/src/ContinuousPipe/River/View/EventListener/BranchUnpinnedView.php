<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Flow\Event\BranchUnpinned;
use ContinuousPipe\River\View\Storage\BranchViewStorage;

class BranchUnpinnedView
{
    private $branchViewStorage;

    public function __construct(BranchViewStorage $branchViewStorage)
    {
        $this->branchViewStorage = $branchViewStorage;
    }

    public function notify(BranchUnpinned $event)
    {
        $this->branchViewStorage->branchUnpinned($event->getFlowUuid(), $event->getBranch());
    }
}
