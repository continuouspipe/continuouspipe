<?php

namespace ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\CommandHandler;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\Aggregate\GoogleContainerBuilder\Command\FetchGCBuildStatus;
use ContinuousPipe\Builder\GoogleContainerBuilder\GoogleContainerBuilderClient;
use ContinuousPipe\Events\Transaction\TransactionManager;
use SimpleBus\Message\Bus\MessageBus;

class FetchGCBuildStatusHandler
{
    /**
     * @var GoogleContainerBuilderClient
     */
    private $containerBuilderClient;

    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param GoogleContainerBuilderClient $containerBuilderClient
     * @param MessageBus $commandBus
     * @param TransactionManager $transactionManager
     */
    public function __construct(GoogleContainerBuilderClient $containerBuilderClient, MessageBus $commandBus, TransactionManager $transactionManager)
    {
        $this->containerBuilderClient = $containerBuilderClient;
        $this->commandBus = $commandBus;
        $this->transactionManager = $transactionManager;
    }

    public function handle(FetchGCBuildStatus $command)
    {
        $status = $this->containerBuilderClient->fetchStatus($command->getGoogleContainerBuild());

        if ($status->isRunning()) {
            $this->commandBus->handle(new FetchGCBuildStatus(
                $command->getBuildIdentifier(),
                $command->getGoogleContainerBuild()
            ));

            return;
        }

        $this->transactionManager->apply($command->getBuildIdentifier(), function (Build $build) use ($status) {
            $build->completeBuild($status);
        });

    }
}