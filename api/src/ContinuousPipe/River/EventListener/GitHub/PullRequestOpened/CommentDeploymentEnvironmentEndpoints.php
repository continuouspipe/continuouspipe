<?php

namespace ContinuousPipe\River\EventListener\GitHub\PullRequestOpened;

use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
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
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param TideRepository     $tideRepository
     * @param TideStatusListener $tideStatusListener
     * @param EventStore         $eventStore
     */
    public function __construct(TideRepository $tideRepository, TideStatusListener $tideStatusListener, EventStore $eventStore)
    {
        $this->tideRepository = $tideRepository;
        $this->eventStore = $eventStore;
        $this->tideStatusListener = $tideStatusListener;
    }

    /**
     * @param PullRequestOpened $event
     */
    public function notify(PullRequestOpened $event)
    {
        $tides = $this->tideRepository->findByCodeReference($event->getFlow()->getUuid(), $event->getCodeReference());

        foreach ($tides as $tide) {
            $events = $this->eventStore->findByTideUuid($tide->getUuid());
            if (false === ($lastEvent = end($events))) {
                continue;
            }

            $this->tideStatusListener->notify($lastEvent);
        }
    }
}
