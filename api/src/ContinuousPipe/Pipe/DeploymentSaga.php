<?php

namespace ContinuousPipe\Pipe;

use ContinuousPipe\Pipe\Command\CreateComponentsCommand;
use ContinuousPipe\Pipe\Command\CreatePublicEndpointsCommand;
use ContinuousPipe\Pipe\Command\PrepareEnvironmentCommand;
use ContinuousPipe\Pipe\Command\ProxyPublicEndpointsCommand;
use ContinuousPipe\Pipe\Command\RollbackDeploymentCommand;
use ContinuousPipe\Pipe\Command\WaitComponentsCommand;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\ComponentsReady;
use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\Event\DeploymentFailed;
use ContinuousPipe\Pipe\Event\DeploymentStarted;
use ContinuousPipe\Pipe\Event\DeploymentSuccessful;
use ContinuousPipe\Pipe\Event\EnvironmentPrepared;
use ContinuousPipe\Pipe\Event\PublicEndpointsFinalised;
use ContinuousPipe\Pipe\Event\PublicEndpointsReady;
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
     * @param MessageBus                $commandBus
     * @param View\DeploymentRepository $deploymentRepository
     * @param MessageBus                $eventBus
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
        } elseif ($event instanceof PublicEndpointsReady) {
            $this->handlePublicEndpointsCreated($event);
        } elseif ($event instanceof DeploymentFailed) {
            $this->commandBus->handle(new RollbackDeploymentCommand($event->getDeploymentContext()));
        } elseif ($event instanceof ComponentsCreated) {
            $this->commandBus->handle(new WaitComponentsCommand($event->getDeploymentContext(), $event->getComponentStatuses()));
        } elseif ($event instanceof ComponentsReady) {
            $this->eventBus->handle(new DeploymentSuccessful($event->getDeploymentUuid()));
        }
    }

    /**
     * @param PublicEndpointsReady $event
     */
    private function handlePublicEndpointsCreated(PublicEndpointsReady $event)
    {
        $this->eventBus->handle(
            new PublicEndpointsFinalised($event->getDeploymentContext(), $event->getEndpoints())
        );
    }
}
