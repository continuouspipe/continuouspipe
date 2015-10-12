<?php

namespace ContinuousPipe\River\Tide;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Tide\Summary\DeployedService;
use ContinuousPipe\River\View\Tide;

class TideSummaryCreator
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
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
            $this->getDeployedServices($tide)
        );
    }

    /**
     * @param Tide $tide
     *
     * @return DeployedService[]
     */
    private function getDeployedServices(Tide $tide)
    {
        $events = $this->eventStore->findByTideUuid($tide->getUuid());
        /** @var DeploymentSuccessful[] $deploymentSuccessfulEvents */
        $deploymentSuccessfulEvents = array_values(array_filter($events, function ($event) {
            return $event instanceof DeploymentSuccessful;
        }));

        if (0 === count($deploymentSuccessfulEvents)) {
            return [];
        }

        $deployment = $deploymentSuccessfulEvents[0]->getDeployment();
        $statuses = $deployment->getComponentStatuses();
        $publicEndpoints = $this->getAssociativePublicEndpoints($deployment->getPublicEndpoints());

        $summary = [];
        foreach ($statuses as $serviceName => $status) {
            $summary[$serviceName] = new DeployedService(
                $status,
                array_key_exists($serviceName, $publicEndpoints) ? $publicEndpoints[$serviceName] : null
            );
        }

        return $summary;
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
}
