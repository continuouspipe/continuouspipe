<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake\Handler;

use ContinuousPipe\Adapter\Kubernetes\Handler\CreateComponentsHandler;
use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\Command\DeploymentCommand;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use SimpleBus\Message\Bus\MessageBus;

class HandlerForSuccessfulDeployment
{
    /**
     * @var MessageBus
     */
    private $messageBus;

    /**
     * @param MessageBus $messageBus
     */
    public function __construct(MessageBus $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @param DeploymentCommand $command
     */
    public function handle(DeploymentCommand $command)
    {
        if ($command instanceof CreateComponentsCommand) {
            $this->messageBus->handle(new ComponentsCreated($command->getContext()));
        } else if ($command instanceof PrepareEnvironmentCommand) {
            $this->messageBus->handle(new EnvironmentPrepared($command->getContext()));
        } else if ($command instanceof CreatePublicEndpointsCommand) {
            $this->messageBus->handle(new PublicEndpointsCreated($command->getContext(), []));
        }
    }
}