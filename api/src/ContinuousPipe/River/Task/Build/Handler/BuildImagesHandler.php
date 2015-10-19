<?php

namespace ContinuousPipe\River\Task\Build\Handler;

use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\Builder\BuildRequestCreator;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Build\Command\BuildImageCommand;
use ContinuousPipe\River\Task\Build\Command\BuildImagesCommand;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use SimpleBus\Message\Bus\MessageBus;

class BuildImagesHandler
{
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var BuildRequestCreator
     */
    private $buildRequestCreator;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus          $commandBus
     * @param MessageBus          $eventBus
     * @param TideRepository      $tideRepository
     * @param BuildRequestCreator $buildRequestCreator
     * @param LoggerFactory       $loggerFactory
     */
    public function __construct(MessageBus $commandBus, MessageBus $eventBus, TideRepository $tideRepository, BuildRequestCreator $buildRequestCreator, LoggerFactory $loggerFactory)
    {
        $this->commandBus = $commandBus;
        $this->tideRepository = $tideRepository;
        $this->eventBus = $eventBus;
        $this->buildRequestCreator = $buildRequestCreator;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param BuildImagesCommand $command
     */
    public function handle(BuildImagesCommand $command)
    {
        $logger = $this->loggerFactory->fromId($command->getLogId());
        $logger->start();

        $tideUuid = $command->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);
        $tideContext = $tide->getContext();

        try {
            $buildRequests = $this->buildRequestCreator->createBuildRequests($tideContext->getCodeReference(), $command->getConfiguration());
        } catch (BuilderException $e) {
            $logger->append(new Text($e->getMessage()));
            $this->eventBus->handle(new ImageBuildsFailed($tideUuid, $logger->getLog()));

            return;
        }

        $this->eventBus->handle(new ImageBuildsStarted($tideUuid, $buildRequests, $logger->getLog()));

        if (empty($buildRequests)) {
            $logger->append(new Text('Found no image to build'));
            $this->eventBus->handle(new ImageBuildsSuccessful($tideUuid, $logger->getLog()));
        }

        foreach ($buildRequests as $buildRequest) {
            $log = $logger->append(new Text(sprintf('Building image \'%s\'', $buildRequest->getImage()->getName())));

            $command = new BuildImageCommand($tideUuid, $buildRequest, $log->getId());
            $this->commandBus->handle($command);
        }
    }
}
