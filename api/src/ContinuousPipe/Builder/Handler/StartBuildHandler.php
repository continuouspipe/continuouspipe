<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildImageCommand;
use ContinuousPipe\Builder\Command\StartBuildCommand;
use ContinuousPipe\Builder\Logging\BuildLoggerFactory;
use SimpleBus\Message\Bus\MessageBus;

class StartBuildHandler
{
    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @var BuildLoggerFactory
     */
    private $loggerFactory;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param BuildRepository    $buildRepository
     * @param BuildLoggerFactory $loggerFactory
     * @param MessageBus         $commandBus
     */
    public function __construct(BuildRepository $buildRepository, BuildLoggerFactory $loggerFactory, MessageBus $commandBus)
    {
        $this->buildRepository = $buildRepository;
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
    }

    /**
     * @param StartBuildCommand $command
     */
    public function handle(StartBuildCommand $command)
    {
        $build = $command->getBuild();

        $logger = $this->loggerFactory->forBuild($build);
        $logger->start();

        $build->updateStatus(Build::STATUS_RUNNING);
        $build = $this->buildRepository->save($build);

        $this->commandBus->handle(new BuildImageCommand($build, $logger));
    }
}
