<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\View\Factory\TideViewFactory;
use ContinuousPipe\River\View\Storage\PullRequestViewStorage;

class TideEventPullRequestView
{
    private $pullRequestViewStorage;
    /**
     * @var TideViewFactory
     */
    private $tideViewFactory;

    public function __construct(PullRequestViewStorage $pullRequestViewStorage, TideViewFactory $tideViewFactory)
    {
        $this->tideViewFactory = $tideViewFactory;
        $this->pullRequestViewStorage = $pullRequestViewStorage;
    }

    public function notify(TideEvent $event)
    {
        $this->pullRequestViewStorage->updateTide($this->tideViewFactory->create($event->getTideUuid()));
    }
}