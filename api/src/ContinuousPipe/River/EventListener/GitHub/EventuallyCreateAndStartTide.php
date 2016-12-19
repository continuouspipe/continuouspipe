<?php

namespace ContinuousPipe\River\EventListener\GitHub;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Pipeline\Command\GenerateTides;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Pipeline\TideGenerationTrigger;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class EventuallyCreateAndStartTide
{
    private $commandBus;
    private $flatFlowRepository;

    public function __construct(MessageBus $commandBus, FlatFlowRepository $flatFlowRepository)
    {
        $this->commandBus = $commandBus;
        $this->flatFlowRepository = $flatFlowRepository;
    }

    /**
     * @param CodeRepositoryEvent $event
     */
    public function notify(CodeRepositoryEvent $event)
    {
        $this->commandBus->handle(new GenerateTides(
            new TideGenerationRequest(
                Uuid::uuid4(),
                $this->flatFlowRepository->find($event->getFlowUuid()),
                $event->getCodeReference(),
                TideGenerationTrigger::codeRepositoryEvent($event)
            )
        ));
    }
}
