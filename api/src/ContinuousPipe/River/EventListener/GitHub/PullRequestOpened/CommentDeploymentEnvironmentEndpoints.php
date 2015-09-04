<?php

namespace ContinuousPipe\River\EventListener\GitHub\PullRequestOpened;

use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\Event\GitHub\PullRequestOpened;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;

class CommentDeploymentEnvironmentEndpoints
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var PullRequestDeploymentNotifier
     */
    private $pullRequestDeploymentNotifier;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param TideRepository                $tideRepository
     * @param PullRequestDeploymentNotifier $pullRequestDeploymentNotifier
     * @param EventStore                    $eventStore
     */
    public function __construct(TideRepository $tideRepository, PullRequestDeploymentNotifier $pullRequestDeploymentNotifier, EventStore $eventStore)
    {
        $this->tideRepository = $tideRepository;
        $this->pullRequestDeploymentNotifier = $pullRequestDeploymentNotifier;
        $this->eventStore = $eventStore;
    }

    /**
     * @param PullRequestOpened $event
     */
    public function notify(PullRequestOpened $event)
    {
        $tides = $this->tideRepository->findByCodeReference($event->getCodeReference());
        $gitHubEvent = $event->getEvent();

        foreach ($tides as $tide) {
            $deploymentSuccessfulEvents = $this->getDeploymentSuccessfulEvents($tide);

            foreach ($deploymentSuccessfulEvents as $deploymentSuccessfulEvent) {
                $this->pullRequestDeploymentNotifier->notify($deploymentSuccessfulEvent, $gitHubEvent->getRepository(), $gitHubEvent->getPullRequest());
            }
        }
    }

    /**
     * Get deployment successful events of a given tide.
     *
     * @param Tide $tide
     *
     * @return DeploymentSuccessful[]
     */
    private function getDeploymentSuccessfulEvents(Tide $tide)
    {
        $tideEvents = $this->eventStore->findByTideUuid($tide->getUuid());
        $deploymentSuccessfulEvents = array_values(array_filter($tideEvents, function (TideEvent $event) {
            return $event instanceof DeploymentSuccessful;
        }));

        return $deploymentSuccessfulEvents;
    }
}
