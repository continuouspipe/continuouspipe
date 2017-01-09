<?php

namespace ContinuousPipe\River\EventListener\GitHub\PullRequestOpened;

use ContinuousPipe\River\CodeRepository\Event\PullRequestOpened;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Notifications\TideStatusListener;
use ContinuousPipe\River\View\TideRepository;

class CommentDeploymentEnvironmentEndpoints
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var TideStatusListener
     */
    private $tideStatusListener;

    /**
     * @param TideRepository     $tideRepository
     * @param TideStatusListener $tideStatusListener
     */
    public function __construct(TideRepository $tideRepository, TideStatusListener $tideStatusListener)
    {
        $this->tideRepository = $tideRepository;
        $this->tideStatusListener = $tideStatusListener;
    }

    /**
     * @param PullRequestOpened $event
     */
    public function notify(PullRequestOpened $event)
    {
        $tides = $this->tideRepository->findByCodeReference($event->getFlowUuid(), $event->getCodeReference());

        foreach ($tides as $tide) {
            $this->tideStatusListener->triggerNotifications($tide);
        }
    }
}
