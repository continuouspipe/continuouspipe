<?php

namespace ContinuousPipe\River\Handler;

use ContinuousPipe\Builder\BuildRequestCreator;
use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\River\Command\BuildImagesCommand;
use ContinuousPipe\River\Event\ImageBuildsStarted;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Command\BuildImageCommand;
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
        $logger = $this->loggerFactory->from($command->getLog());
        $logger->start();

        $tideUuid = $command->getTideUuid();
        $tide = $this->tideRepository->find($tideUuid);

        $codeRepository = $tide->getCodeRepository();
        try {
            $buildRequests = $this->buildRequestCreator->createBuildRequests($codeRepository, $tide->getCodeReference(), $tide->getUser());
            if (empty($buildRequests)) {
                throw new \RuntimeException('No image to build');
            }
        } catch (FileNotFound $e) {
            $buildRequests = [];
        }

        $this->eventBus->handle(new ImageBuildsStarted($tideUuid, $buildRequests, $command->getLog()));

        foreach ($buildRequests as $buildRequest) {
            $log = $logger->append(new Text(sprintf('Building image \'%s\'', $buildRequest->getImage()->getName())));

            $command = new BuildImageCommand($tideUuid, $buildRequest, $log);
            $this->commandBus->handle($command);
        }
    }
}
