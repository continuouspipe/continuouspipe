<?php

namespace ContinuousPipe\River\EventListener\External;

use ContinuousPipe\River\Command\StartTideCommand;
use ContinuousPipe\River\Event\External\CodePushedEvent;
use ContinuousPipe\River\Repository\FlowRepository;
use League\Tactician\CommandBus;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Rhumsaa\Uuid\Uuid;
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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus $commandBus
     * @param FlowRepository $flowRepository
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(MessageBus $commandBus, FlowRepository $flowRepository, LoggerFactory $loggerFactory)
    {
        $this->commandBus = $commandBus;
        $this->flowRepository = $flowRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param CodePushedEvent $event
     */
    public function notify(CodePushedEvent $event)
    {
        $repository = $event->getRepository();
        $flow = $this->flowRepository->findOneByRepositoryIdentifier($repository->getIdentifier());

        $logger = $this->loggerFactory->create();
        $logger->append(new Text('Starting Tide'));

        $startCommand = new StartTideCommand($event->getUuid(), $flow, $event->getCodeReference(), $logger->getLog());
        $this->commandBus->handle($startCommand);
    }
}
