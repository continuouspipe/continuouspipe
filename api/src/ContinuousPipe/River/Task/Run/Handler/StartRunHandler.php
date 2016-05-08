<?php

namespace ContinuousPipe\River\Task\Run\Handler;

use ContinuousPipe\Pipe\Client;
use ContinuousPipe\River\Task\Run\Command\StartRunCommand;
use ContinuousPipe\River\Task\Run\Event\RunStarted;
use ContinuousPipe\River\Task\Run\RunRequest\DeploymentRequestFactory;
use ContinuousPipe\River\View\TideRepository;
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
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param Client                   $pipeClient
     * @param DeploymentRequestFactory $deploymentRequestFactory
     * @param MessageBus               $eventBus
     * @param TideRepository           $tideRepository
     */
    public function __construct(Client $pipeClient, DeploymentRequestFactory $deploymentRequestFactory, MessageBus $eventBus, TideRepository $tideRepository)
    {
        $this->pipeClient = $pipeClient;
        $this->eventBus = $eventBus;
        $this->deploymentRequestFactory = $deploymentRequestFactory;
        $this->tideRepository = $tideRepository;
    }

    /**
     * @param StartRunCommand $command
     */
    public function handle(StartRunCommand $command)
    {
        $taskDetails = $command->getTaskDetails();

        $tide = $this->tideRepository->find($command->getTideUuid());
        $deploymentRequest = $this->deploymentRequestFactory->createDeploymentRequest($tide, $taskDetails, $command->getConfiguration());
        $deployment = $this->pipeClient->start($deploymentRequest, $tide->getUser());

        $this->eventBus->handle(new RunStarted(
            $tide->getUuid(),
            $taskDetails->getIdentifier(),
            $deployment->getUuid()
        ));
    }
}
