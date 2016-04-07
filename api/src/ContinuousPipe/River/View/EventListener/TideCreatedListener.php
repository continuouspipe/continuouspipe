<?php

namespace ContinuousPipe\River\View\EventListener;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\River\View\TimeResolver;

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
     * @var TimeResolver
     */
    private $timeResolver;

    /**
     * @param TideRepository $tideRepository
     * @param FlowRepository $flowRepository
     * @param TimeResolver   $timeResolver
     */
    public function __construct(TideRepository $tideRepository, FlowRepository $flowRepository, TimeResolver $timeResolver)
    {
        $this->tideRepository = $tideRepository;
        $this->flowRepository = $flowRepository;
        $this->timeResolver = $timeResolver;
    }

    /**
     * @param TideCreated $event
     */
    public function notify(TideCreated $event)
    {
        $tideContext = $event->getTideContext();
        $flow = $this->flowRepository->find($tideContext->getFlowUuid());

        $view = Tide::create(
            $event->getTideUuid(),
            Flow::fromFlow($flow),
            $tideContext->getCodeReference(),
            $tideContext->getLog(),
            $tideContext->getTeam(),
            $tideContext->getUser(),
            $tideContext->getConfiguration(),
            $this->timeResolver->resolve()
        );

        $view->setStatus(Tide::STATUS_PENDING);

        $this->tideRepository->save($view);
    }
}
