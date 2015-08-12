<?php

namespace ContinuousPipe\River\Task\Deploy\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Task\Deploy\Command\StartDeploymentCommand;
use ContinuousPipe\River\Task\Deploy\DeploymentRequestFactory;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
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
     * @param DeploymentRequestFactory $deploymentRequestFactory
     * @param Client                   $pipeClient
     * @param MessageBus               $eventBus
     */
    public function __construct(DeploymentRequestFactory $deploymentRequestFactory, Client $pipeClient, MessageBus $eventBus)
    {
        $this->deploymentRequestFactory = $deploymentRequestFactory;
        $this->pipeClient = $pipeClient;
        $this->eventBus = $eventBus;
    }

    /**
     * @param StartDeploymentCommand $command
     */
    public function handle(StartDeploymentCommand $command)
    {
        $deployContext = $command->getDeployContext();
        $deploymentRequest = $this->deploymentRequestFactory->create($deployContext);

        $this->pipeClient->start($deploymentRequest, $deployContext->getUser());

        $this->eventBus->handle(new DeploymentStarted($command->getTideUuid(), $deploymentRequest));
    }
}
