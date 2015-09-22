<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class TideCreatedListener
{
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param TideRepository $tideRepository
     * @param FlowRepository $flowRepository
     */
    public function __construct(TideRepository $tideRepository, FlowRepository $flowRepository)
    {
        $this->tideRepository = $tideRepository;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @param TideCreated $event
     */
    public function notify(TideCreated $event)
    {
        $tideContext = $event->getTideContext();
        $flow = $this->flowRepository->find($tideContext->getFlowUuid());

        $view = Tide::create($event->getTideUuid(), Flow::fromFlow($flow), $tideContext->getCodeReference(), $tideContext->getLog(), $tideContext->getUser(), $tideContext->getConfiguration());
        $view->setStatus(Tide::STATUS_PENDING);

        $this->tideRepository->save($view);
    }
}
