<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\Command\ProxyPublicEndpointsCommand;
use ContinuousPipe\Pipe\Environment\ProxiedPublicEndpoint;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Event\PublicEndpointsCreated;
use ContinuousPipe\Pipe\Event\PublicEndpointsFinalised;
use SimpleBus\Message\Bus\MessageBus;

class DeploymentSaga
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var View\DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $commandBus
     * @param View\DeploymentRepository $deploymentRepository
     * @param MessageBus $eventBus
     */
    public function __construct(
        MessageBus $commandBus,
        View\DeploymentRepository $deploymentRepository,
        MessageBus $eventBus
    ) {
        $this->commandBus = $commandBus;
        $this->deploymentRepository = $deploymentRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * @param DeploymentEvent $event
     */
    public function notify(DeploymentEvent $event)
    {
        if ($event instanceof DeploymentStarted) {
            $this->commandBus->handle(new PrepareEnvironmentCommand($event->getDeploymentContext()));
        } elseif ($event instanceof EnvironmentPrepared) {
            $this->commandBus->handle(new CreatePublicEndpointsCommand($event->getDeploymentContext()));
        } elseif ($event instanceof PublicEndpointsFinalised) {
            $this->commandBus->handle(new CreateComponentsCommand($event->getDeploymentContext()));
        } elseif ($event instanceof ComponentsCreated) {
            $this->eventBus->handle(
                new DeploymentSuccessful(
                    $event->getDeploymentUuid()
                )
            );
        } elseif ($event instanceof PublicEndpointsCreated) {
            $this->handlePublicEndpointsCreated($event);
        }
    }

    /**
     * @param PublicEndpointsCreated $event
     */
    private function handlePublicEndpointsCreated(PublicEndpointsCreated $event)
    {
        if ($this->hasProxiedEndpoints($event)) {
            $this->commandBus->handle(
                new ProxyPublicEndpointsCommand($event->getDeploymentContext(), $event->getEndpoints())
            );

            return;
        }

        $this->eventBus->handle(
            new PublicEndpointsFinalised($event->getDeploymentContext(), $event->getEndpoints())
        );
    }

    /**
     * @param PublicEndpointsCreated $event
     * @return mixed
     */
    private function hasProxiedEndpoints(PublicEndpointsCreated $event)
    {
        return array_reduce(
            $event->getEndpoints(),
            function ($hasProxiedEndpoints, $endpoint) {
                return $endpoint instanceof ProxiedPublicEndpoint ? true : $hasProxiedEndpoints;
            },
            false
        );
    }
}
