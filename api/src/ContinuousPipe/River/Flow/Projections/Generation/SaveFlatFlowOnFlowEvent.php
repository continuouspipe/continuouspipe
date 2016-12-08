<?php

namespace ContinuousPipe\River\Flow\Projections\Generation;

use ContinuousPipe\River\Flow\Event\FlowEvent;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Repository\FlowRepository;

class SaveFlatFlowOnFlowEvent
{
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param FlatFlowRepository $flatFlowRepository
     * @param FlowRepository     $flowRepository
     */
    public function __construct(FlatFlowRepository $flatFlowRepository, FlowRepository $flowRepository)
    {
        $this->flatFlowRepository = $flatFlowRepository;
        $this->flowRepository = $flowRepository;
    }

    public function notify(FlowEvent $event)
    {
        $flow = $this->flowRepository->find($event->getFlowUuid());

        $this->flatFlowRepository->save(
            FlatFlow::fromFlow($flow)
        );
    }
}
