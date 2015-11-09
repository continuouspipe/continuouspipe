<?php

namespace ContinuousPipe\Pipe\Tests\Adapter\Fake\Handler;

use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\Command\DeploymentCommand;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Event\PublicEndpointsReady;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Pipe\Tests\Cluster\TestCluster;
use SimpleBus\Message\Bus\MessageBus;

class HandlerForSuccessfulDeployment implements DeploymentHandler
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
            $this->messageBus->handle(new ComponentsCreated($command->getContext(), []));
        } elseif ($command instanceof PrepareEnvironmentCommand) {
            $this->messageBus->handle(new EnvironmentPrepared($command->getContext()));
        } elseif ($command instanceof CreatePublicEndpointsCommand) {
            $this->messageBus->handle(new PublicEndpointsReady($command->getContext(), []));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof TestCluster;
    }
}
