<?php

namespace ContinuousPipe\Adapter\Kubernetes\Handler;

use ContinuousPipe\Adapter\Kubernetes\Client\DeploymentClientFactory;
use ContinuousPipe\Adapter\Kubernetes\KubernetesAdapter;
use ContinuousPipe\Pipe\Command\RollbackDeploymentCommand;
use ContinuousPipe\Pipe\DeploymentContext;
use ContinuousPipe\Pipe\Event\ComponentsCreated;
use ContinuousPipe\Pipe\Event\DeploymentEvent;
use ContinuousPipe\Pipe\EventBus\EventStore;
use ContinuousPipe\Pipe\Handler\Deployment\DeploymentHandler;
use ContinuousPipe\Pipe\View\ComponentStatus;
use Kubernetes\Client\Exception\ReplicationControllerNotFound;
use Kubernetes\Client\Exception\ServiceNotFound;
use Rhumsaa\Uuid\Uuid;

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

    public function handle(RollbackDeploymentCommand $command)
    {
        $context = $command->getContext();
        $deploymentUuid = $context->getDeployment()->getUuid();
        if (null === ($componentsCreatedEvent = $this->getComponentsCreatedEvent($deploymentUuid))) {
            return;
        }

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
     * {@inheritdoc}
     */
    public function supports(DeploymentContext $context)
    {
        return $context->getProvider()->getAdapterType() == KubernetesAdapter::TYPE;
    }

    /**
     * @param Uuid $deploymentUuid
     *
     * @return ComponentsCreated
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
}
