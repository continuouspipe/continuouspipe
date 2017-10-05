<?php

namespace ContinuousPipe\Pipe\Kubernetes\Handler;

use ContinuousPipe\Pipe\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Pipe\Command\RollbackDeploymentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\EventBus\EventStore;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Pipe\View\ComponentStatus;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Exception\DeploymentNotFound;
use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Deployment\DeploymentRollback;
use Kubernetes\Client\Model\Deployment\RollbackConfiguration;
use Ramsey\Uuid\Uuid;

class RollbackDeploymentHandler implements DeploymentHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var DeploymentClientFactory
     */
    private $clientFactory;

    /**
     * @param EventStore              $eventStore
     * @param DeploymentClientFactory $clientFactory
     */
    public function __construct(EventStore $eventStore, DeploymentClientFactory $clientFactory)
    {
        $this->eventStore = $eventStore;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param RollbackDeploymentCommand $command
     */
    public function handle(RollbackDeploymentCommand $command)
    {
        $context = $command->getContext();
        $deploymentUuid = $context->getDeployment()->getUuid();
        if (null === ($componentsCreatedEvent = $this->getComponentsCreatedEvent($deploymentUuid))) {
            return;
        }

        $this->rollbackUpdatedOrCreatedDeployments($context, $componentsCreatedEvent);
        $this->removeCreatedServicesAndReplicationControllers($componentsCreatedEvent, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getCluster() instanceof Kubernetes;
    }

    /**
     * @param Uuid $deploymentUuid
     *
     * @return ComponentsCreated|null
     */
    private function getComponentsCreatedEvent(Uuid $deploymentUuid)
    {
        $events = $this->eventStore->findByDeploymentUuid($deploymentUuid);
        $matchingEvents = array_filter($events, function (DeploymentEvent $event) {
            return $event instanceof ComponentsCreated;
        });

        if (0 === count($matchingEvents)) {
            return;
        }

        return current($matchingEvents);
    }

    /**
     * @param $componentsCreatedEvent
     * @param $context
     */
    private function removeCreatedServicesAndReplicationControllers(ComponentsCreated $componentsCreatedEvent, DeploymentContext $context)
    {
        $createdComponentNames = array_keys(array_filter($componentsCreatedEvent->getComponentStatuses(), function (ComponentStatus $status) {
            return $status->isCreated();
        }));

        if (0 === count($createdComponentNames)) {
            return;
        }

        $client = $this->clientFactory->get($context);
        $replicationControllerRepository = $client->getReplicationControllerRepository();
        $serviceRepository = $client->getServiceRepository();

        foreach ($createdComponentNames as $componentName) {
            try {
                $replicationController = $replicationControllerRepository->findOneByName($componentName);
                $replicationControllerRepository->delete($replicationController);
            } catch (ReplicationControllerNotFound $e) {
            }

            try {
                $service = $serviceRepository->findOneByName($componentName);
                $serviceRepository->delete($service);
            } catch (ServiceNotFound $e) {
            }
        }
    }

    /**
     * @param DeploymentContext $context
     * @param ComponentsCreated $componentsCreatedEvent
     */
    private function rollbackUpdatedOrCreatedDeployments(DeploymentContext $context, ComponentsCreated $componentsCreatedEvent)
    {
        $createdComponentNames = array_keys(array_filter($componentsCreatedEvent->getComponentStatuses(), function (ComponentStatus $status) {
            return $status->isCreated() || $status->isUpdated();
        }));

        $client = $this->clientFactory->get($context);
        $deploymentsRepository = $client->getDeploymentRepository();

        foreach ($createdComponentNames as $componentName) {
            try {
                $deployment = $deploymentsRepository->findOneByName($componentName);
                $deploymentsRepository->rollback(new DeploymentRollback(
                    $deployment->getMetadata()->getName(),
                    new RollbackConfiguration(0)
                ));
            } catch (DeploymentNotFound $e) {
            }
        }
    }
}
