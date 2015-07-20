<?php

namespace ContinuousPipe\River\EventListener\External;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\External\CodePushedEvent;
use ContinuousPipe\River\Repository\FlowRepository;
use League\Tactician\CommandBus;
use SimpleBus\Message\Bus\MessageBus;

class CodePushedListener
{
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param MessageBus     $commandBus
     * @param FlowRepository $flowRepository
     */
    public function __construct(MessageBus $commandBus, FlowRepository $flowRepository)
    {
        $this->commandBus = $commandBus;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @param CodePushedEvent $event
     */
    public function notify(CodePushedEvent $event)
    {
        $repository = $event->getRepository();
        $flow = $this->flowRepository->findOneByRepositoryIdentifier($repository->getIdentifier());

        $startCommand = new StartTideCommand($flow, $event->getCodeReference());
        $this->commandBus->handle($startCommand);
    }
}
