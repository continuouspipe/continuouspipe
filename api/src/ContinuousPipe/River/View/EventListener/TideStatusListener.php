<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class TideStatusListener
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
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $view = $this->tideRepository->find($event->getTideUuid());

        if ($event instanceof TideStarted) {
            $view->setStatus(Tide::STATUS_RUNNING);
        } elseif ($event instanceof TideFailed) {
            $view->setStatus(Tide::STATUS_FAILURE);
        } elseif ($event instanceof TideSuccessful) {
            $view->setStatus(Tide::STATUS_SUCCESS);
        }

        $this->tideRepository->save($view);
    }
}
