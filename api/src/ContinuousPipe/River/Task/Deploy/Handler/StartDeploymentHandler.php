<?php

namespace ContinuousPipe\River\Task\Deploy\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Naming\UnresolvedEnvironmentNameException;
use ContinuousPipe\River\View\TideRepository;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class StartDeploymentHandler
{
    /**
     * @var DeploymentRequestFactory
     */
    private $deploymentRequestFactory;

    /**
     * @var Client
     */
    private $pipeClient;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param DeploymentRequestFactory $deploymentRequestFactory
     * @param Client                   $pipeClient
     * @param MessageBus               $eventBus
     * @param LoggerFactory            $loggerFactory
     * @param TideRepository           $tideRepository
     */
    public function __construct(DeploymentRequestFactory $deploymentRequestFactory, Client $pipeClient, MessageBus $eventBus, LoggerFactory $loggerFactory, TideRepository $tideRepository)
    {
        $this->deploymentRequestFactory = $deploymentRequestFactory;
        $this->pipeClient = $pipeClient;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param StartDeploymentCommand $command
     */
    public function handle(StartDeploymentCommand $command)
    {
        $taskDetails = $command->getTaskDetails();
        $tide = $this->tideRepository->find($command->getTideUuid());

        try {
            $deploymentRequest = $this->deploymentRequestFactory->create($tide, $taskDetails, $command->getConfiguration());
        } catch (UnresolvedEnvironmentNameException $e) {
            $this->eventBus->handle(new TideFailed($command->getTideUuid(), $e->getMessage()));

            $logger = $this->loggerFactory->fromId($taskDetails->getLogId());
            $logger->child(new Text($e->getMessage()));

            return;
        }

        try {
            $deployment = $this->pipeClient->start($deploymentRequest, $tide->getUser());
            $this->eventBus->handle(new DeploymentStarted($command->getTideUuid(), $deployment, $taskDetails->getIdentifier()));
        } catch (\Exception $e) {
            $failedDeployment = new Client\Deployment(Uuid::fromString(Uuid::NIL), $deploymentRequest, Client\Deployment::STATUS_FAILURE);
            $this->eventBus->handle(new DeploymentFailed($command->getTideUuid(), $failedDeployment, $taskDetails->getIdentifier()));

            $logger = $this->loggerFactory->fromId($taskDetails->getLogId());
            $logger->child(new Text(sprintf(
                'PANIC (%s): %s',
                get_class($e),
                $e->getMessage()
            )));
        }
    }
}
