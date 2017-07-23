<?php

namespace ContinuousPipe\River\Tide;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Tide\Summary\CurrentTask;
use ContinuousPipe\River\Tide\Summary\DeployedService;
use ContinuousPipe\River\Tide\Summary\Environment;
use ContinuousPipe\River\View\Tide;
use Psr\Log\LoggerInterface;

class TideSummaryCreator
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EventStore $eventStore
     * @param TideRepository $tideRepository
     * @param LoggerInterface $logger
     */
    public function __construct(EventStore $eventStore, TideRepository $tideRepository, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
        $this->logger = $logger;
    }

    /**
     * @param Tide $tide
     *
     * @return TideSummary
     */
    public function fromTide(Tide $tide)
    {
        return new TideSummary(
            $tide->getStatus(),
            $this->getDeployedServices($this->getDeploymentSuccessfulEventsForTide($tide)),
            $this->getCurrentTask($tide),
            $this->getEnvironment($this->getDeploymentStartedEventsForTide($tide))
        );
    }

    /**
     * @param DeploymentSuccessful[] $deploymentSuccessfulEvents
     * @return Summary\DeployedService[]
     *
     */
    private function getDeployedServices(array $deploymentSuccessfulEvents)
    {
        if (0 === count($deploymentSuccessfulEvents)) {
            return [];
        }

        $services = [];

        foreach ($deploymentSuccessfulEvents as $deploymentSuccessful) {
            $services = array_merge($services, $this->getDeployedServicesFromEvent($deploymentSuccessful));
        }

        return $services;
    }

    private function getDeployedServicesFromEvent(DeploymentSuccessful $event)
    {
        $deployment = $event->getDeployment();
        $statuses = $deployment->getComponentStatuses();
        $publicEndpoints = $this->getAssociativePublicEndpoints($deployment->getPublicEndpoints());

        $summary = [];
        foreach ($statuses as $serviceName => $status) {
            if (array_key_exists($serviceName, $publicEndpoints)) {
                $endpoint = $publicEndpoints[$serviceName];

                unset($publicEndpoints[$serviceName]);
            } else {
                $endpoint = null;
            }

            $summary[$serviceName] = new DeployedService($status, $endpoint);
        }

        foreach ($publicEndpoints as $name => $endpoint) {
            $summary[$name] = new DeployedService(null, $endpoint);
        }

        return $summary;
    }

    /**
     * @param DeploymentStarted[] $deploymentStartedEvents
     *
     * @return Environment|null
     */
    private function getEnvironment(array $deploymentStartedEvents)
    {
        $environments = array_unique(array_map(function (DeploymentStarted $event) {
            $target = $event->getDeployment()->getRequest()->getTarget();

            return new Environment($target->getEnvironmentName(), $target->getClusterIdentifier());
        }, $deploymentStartedEvents));

        if (count($environments) == 0) {
            return null;
        } elseif (count($environments) != 1) {
            $this->logger->warning('Summary is tricky: tide deployed on multiple environments', [
                'tide_uuid' => (string) $deploymentStartedEvents[0]->getTideUuid(),
            ]);
        }

        return $environments[0];
    }

    /**
     * @param PublicEndpoint[] $endpoints
     *
     * @return array
     */
    private function getAssociativePublicEndpoints(array $endpoints)
    {
        $array = [];

        foreach ($endpoints as $endpoint) {
            $array[$endpoint->getName()] = $endpoint;
        }

        return $array;
    }

    /**
     * @param Tide $tideView
     *
     * @return CurrentTask|null
     */
    private function getCurrentTask(Tide $tideView)
    {
        if ($tideView->getStatus() != Tide::STATUS_RUNNING) {
            return;
        }

        $tide = $this->tideRepository->find($tideView->getUuid());
        if ($task = $tide->getTasks()->getCurrentTask()) {
            return new CurrentTask(
                $task->getIdentifier(),
                $task->getLabel()
            );
        }

        return;
    }

    /**
     * @return DeploymentSuccessful[]
     */
    private function getDeploymentSuccessfulEventsForTide(Tide $tide): array
    {
        return $this->getFilteredEvents($tide,
            function ($event) {
                return $event instanceof DeploymentSuccessful;
            }
        );
    }

    /**
     * @return DeploymentStarted[]
     */
    private function getDeploymentStartedEventsForTide(Tide $tide): array
    {
        return $this->getFilteredEvents($tide,
            function ($event) {
                return $event instanceof DeploymentStarted;
            }
        );
    }

    private function getFilteredEvents(Tide $tide, $filter): array
    {
        return array_values(
            array_filter(
                $this->eventStore->findByTideUuid($tide->getUuid()),
                $filter
            )
        );
    }
}
