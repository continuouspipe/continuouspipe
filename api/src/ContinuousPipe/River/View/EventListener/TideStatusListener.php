<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\River\View\TimeResolver;

class TideStatusListener
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var TimeResolver
     */
    private $timeResolver;

    /**
     * @param TideRepository $tideRepository
     * @param TimeResolver   $timeResolver
     */
    public function __construct(TideRepository $tideRepository, TimeResolver $timeResolver)
    {
        $this->tideRepository = $tideRepository;
        $this->timeResolver = $timeResolver;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $view = $this->tideRepository->find($event->getTideUuid());

        if ($event instanceof TideStarted) {
            $view->setStatus(Tide::STATUS_RUNNING);
            $view->setStartDate($this->timeResolver->resolve());
        } elseif ($event instanceof TideFailed) {
            $view->setStatus(Tide::STATUS_FAILURE);
            $view->setFinishDate($this->timeResolver->resolve());
        } elseif ($event instanceof TideSuccessful) {
            $view->setStatus(Tide::STATUS_SUCCESS);
            $view->setFinishDate($this->timeResolver->resolve());
        }

        $this->tideRepository->save($view);
    }
}
