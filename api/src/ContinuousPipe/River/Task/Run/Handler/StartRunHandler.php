<?php

namespace ContinuousPipe\River\Task\Run\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\RunRequest\DeploymentRequestFactory;
use SimpleBus\Message\Bus\MessageBus;

class StartRunHandler
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var Client
     */
    private $pipeClient;

    /**
     * @var DeploymentRequestFactory
     */
    private $deploymentRequestFactory;

    /**
     * @param Client                   $pipeClient
     * @param DeploymentRequestFactory $deploymentRequestFactory
     * @param MessageBus               $eventBus
     */
    public function __construct(Client $pipeClient, DeploymentRequestFactory $deploymentRequestFactory, MessageBus $eventBus)
    {
        $this->pipeClient = $pipeClient;
        $this->eventBus = $eventBus;
        $this->deploymentRequestFactory = $deploymentRequestFactory;
    }

    /**
     * @param StartRunCommand $command
     */
    public function handle(StartRunCommand $command)
    {
        $context = $command->getContext();

        $deploymentRequest = $this->deploymentRequestFactory->createDeploymentRequest($context, $command->getConfiguration());
        $deployment = $this->pipeClient->start($deploymentRequest, $context->getUser());

        $this->eventBus->handle(new RunStarted(
            $context->getTideUuid(),
            $command->getTaskId(),
            $deployment->getUuid()
        ));
    }
}
