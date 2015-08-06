<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class TideCreatedListener
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param TideRepository $tideRepository
     */
    public function __construct(TideRepository $tideRepository)
    {
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param TideCreated $event
     */
    public function notify(TideCreated $event)
    {
        $view = Tide::create($event->getTideUuid(), $event->getFlow(), $event->getCodeReference(), $event->getParentLog());
        $view->setStatus(Tide::STATUS_PENDING);

        $this->tideRepository->save($view);
    }
}
