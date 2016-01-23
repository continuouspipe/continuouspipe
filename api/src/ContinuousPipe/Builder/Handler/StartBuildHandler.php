<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildImageCommand;
use ContinuousPipe\Builder\Command\StartBuildCommand;
use ContinuousPipe\Builder\Logging\BuildLoggerFactory;
use LogStream\Log;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param BuildRepository    $buildRepository
     * @param BuildLoggerFactory $loggerFactory
     * @param MessageBus         $commandBus
     * @param LoggerInterface    $logger
     */
    public function __construct(BuildRepository $buildRepository, BuildLoggerFactory $loggerFactory, MessageBus $commandBus, LoggerInterface $logger)
    {
        $this->buildRepository = $buildRepository;
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->logger = $logger;
    }

    /**
     * @param StartBuildCommand $command
     */
    public function handle(StartBuildCommand $command)
    {
        $build = $command->getBuild();

        $logger = $this->loggerFactory->forBuild($build)->updateStatus(Log::RUNNING);

        $build->updateStatus(Build::STATUS_RUNNING);
        $build = $this->buildRepository->save($build);

        $this->logger->info('Starting a new build', [
            'build' => $build,
        ]);

        $this->commandBus->handle(new BuildImageCommand($build, $logger));
    }
}
