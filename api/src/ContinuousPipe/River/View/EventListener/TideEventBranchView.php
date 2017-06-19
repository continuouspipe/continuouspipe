<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\View\Factory\TideViewFactory;
use ContinuousPipe\River\View\Storage\BranchViewStorage;

class TideEventBranchView
{
    private $branchViewStorage;
    /**
     * @var TideViewFactory
     */
    private $tideViewFactory;

    public function __construct(BranchViewStorage $branchViewStorage, TideViewFactory $tideViewFactory)
    {
        $this->branchViewStorage = $branchViewStorage;
        $this->tideViewFactory = $tideViewFactory;
    }

    public function notify(TideEvent $event)
    {
        $this->branchViewStorage->updateTide($this->tideViewFactory->create($event->getTideUuid()));
    }
}
