<?php

namespace ContinuousPipe\River\EventListener\External;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\External\CodePushedEvent;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Tide;
use League\Tactician\CommandBus;

class CodePushedListener
{
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param CommandBus $commandBus
     * @param FlowRepository $flowRepository
     */
    public function __construct(CommandBus $commandBus, FlowRepository $flowRepository)
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
