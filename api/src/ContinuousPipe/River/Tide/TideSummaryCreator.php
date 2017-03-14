<?php

namespace ContinuousPipe\River\Tide;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\Tide\Summary\CurrentTask;
use ContinuousPipe\River\Tide\Summary\DeployedService;
use ContinuousPipe\River\View\Tide;

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
     * @param EventStore     $eventStore
     * @param TideRepository $tideRepository
     */
    public function __construct(EventStore $eventStore, TideRepository $tideRepository)
    {
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
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
            $this->getDeployedServices($tide),
            $this->getCurrentTask($tide)
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
}
