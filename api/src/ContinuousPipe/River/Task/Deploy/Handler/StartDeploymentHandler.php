<?php

namespace ContinuousPipe\River\Task\Deploy\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentFailed;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
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
     * @param DeploymentRequestFactory $deploymentRequestFactory
     * @param Client                   $pipeClient
     * @param MessageBus               $eventBus
     * @param LoggerFactory            $loggerFactory
     */
    public function __construct(DeploymentRequestFactory $deploymentRequestFactory, Client $pipeClient, MessageBus $eventBus, LoggerFactory $loggerFactory)
    {
        $this->deploymentRequestFactory = $deploymentRequestFactory;
        $this->pipeClient = $pipeClient;
        $this->eventBus = $eventBus;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param StartDeploymentCommand $command
     */
    public function handle(StartDeploymentCommand $command)
    {
        $deployContext = $command->getDeployContext();
        $deploymentRequest = $this->deploymentRequestFactory->create($deployContext);

        try {
            $deployment = $this->pipeClient->start($deploymentRequest, $deployContext->getUser());
            $this->eventBus->handle(new DeploymentStarted($command->getTideUuid(), $deployment));
        } catch (\Exception $e) {
            $failedDeployment = new Client\Deployment(Uuid::fromString(Uuid::NIL), $deploymentRequest, Client\Deployment::STATUS_FAILURE);
            $this->eventBus->handle(new DeploymentFailed($command->getTideUuid(), $failedDeployment));

            $logger = $this->loggerFactory->from($deployContext->getLog());
            $logger->append(new Text(sprintf(
                'PANIC (%s): %s',
                get_class($e),
                $e->getMessage()
            )));
        }
    }
}
