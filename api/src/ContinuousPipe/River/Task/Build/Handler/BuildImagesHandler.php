<?php

namespace ContinuousPipe\River\Task\Build\Handler;

use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\Builder\BuildRequestCreator;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Build\Command\BuildImageCommand;
use ContinuousPipe\River\Task\Build\Command\BuildImagesCommand;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsFailed;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsStarted;
use ContinuousPipe\River\Task\Build\Event\ImageBuildsSuccessful;
use LogStream\Log;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MessageBus          $commandBus
     * @param MessageBus          $eventBus
     * @param TideRepository      $tideRepository
     * @param BuildRequestCreator $buildRequestCreator
     * @param LoggerFactory       $loggerFactory
     * @param LoggerInterface     $logger
     */
    public function __construct(MessageBus $commandBus, MessageBus $eventBus, TideRepository $tideRepository, BuildRequestCreator $buildRequestCreator, LoggerFactory $loggerFactory, LoggerInterface $logger)
    {
        $this->commandBus = $commandBus;
        $this->tideRepository = $tideRepository;
        $this->eventBus = $eventBus;
        $this->buildRequestCreator = $buildRequestCreator;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    /**
     * @param BuildImagesCommand $command
     */
    public function handle(BuildImagesCommand $command)
    {
        $logger = $this->loggerFactory->fromId($command->getLogId())->updateStatus(Log::RUNNING);

        $tideUuid = $command->getTideUuid();

        try {
            $tide = $this->tideRepository->find($tideUuid);
        } catch (TideNotFound $e) {
            $this->logger->critical('Tide not found while starting to build images', [
                'tideUuid' => (string) $tideUuid,
            ]);

            return;
        }

        $tideContext = $tide->getContext();

        try {
            $buildRequests = $this->buildRequestCreator->createBuildRequests(
                $tideContext->getCodeReference(),
                $command->getConfiguration(),
                $tideContext->getTeam()->getBucketUuid()
            );
        } catch (BuilderException $e) {
            $logger->child(new Text($e->getMessage()));
            $this->eventBus->handle(new ImageBuildsFailed($tideUuid, $logger->getLog()));

            return;
        }

        $this->eventBus->handle(new ImageBuildsStarted($tideUuid, $buildRequests, $logger->getLog()));

        if (empty($buildRequests)) {
            $logger->child(new Text('Found no image to build'));
            $this->eventBus->handle(new ImageBuildsSuccessful($tideUuid, $logger->getLog()));
        }

        foreach ($buildRequests as $buildRequest) {
            $command = new BuildImageCommand($tideUuid, $buildRequest, $logger->getLog()->getId());
            $this->commandBus->handle($command);
        }
    }
}
