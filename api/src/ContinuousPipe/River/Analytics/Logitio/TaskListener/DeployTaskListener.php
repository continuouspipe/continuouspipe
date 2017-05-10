<?php

namespace ContinuousPipe\River\Analytics\Logitio\TaskListener;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;
use ContinuousPipe\River\Analytics\Logitio\Normalizer\DeploymentNormalizer;
use ContinuousPipe\River\Event\TideEventWithMetadata;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentEvent;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentStarted;
use Psr\Log\LoggerInterface;

class DeployTaskListener
{
    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var LogitioClient
     */
    private $logitioClient;

    public function __construct(LogitioClient $logitioClient, EventStore $eventStore, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
        $this->logitioClient = $logitioClient;
    }

    public function notify(DeploymentEvent $event)
    {
        $deployment = $event->getDeployment();

        if ($deployment->getStatus() == Deployment::STATUS_RUNNING || $deployment->getStatus() == Deployment::STATUS_PENDING) {
            return;
        }

        if (null === ($deploymentStarted = $this->getDeploymentStartedEventWithMetadata($event))) {
            $this->logger->critical('Unable to find deployment started event for this deploy task', [
                'eventClass' => get_class($event),
                'deploymentUuid' => (string) $deployment->getUuid(),
                'tideUuid' => (string) $event->getTideUuid(),
                'status' => $deployment->getStatus(),
            ]);

            return;
        }


        if ($deployment->getStatus() == Deployment::STATUS_SUCCESS) {
            $statusCode = 202;
        } elseif ($deployment->getStatus() == Deployment::STATUS_FAILURE) {
            $statusCode = 503;
        } else {
            $statusCode = 501;
        }

        $startedAt = $deploymentStarted->getDateTime();
        $finishedAt = new \DateTime();

        $this->logitioClient->addEvent('DeployLog', [
                'deployment_uuid' => (string) $deployment->getUuid(),
                'tide_uuid' => (string) $event->getTideUuid(),
                'duration' => $finishedAt->getTimestamp() - $startedAt->getTimestamp(),
                'status' => [
                    //'debug_identifier' => ???
                    //'reason' => ???
                    'code' => $statusCode
                ],
                //'username' => ???,
                //'project' => $tide->getTeam()->getName(),
                //'flow_name' => ???
                'timestamp' => time(),
            ]
        );
    }

    /**
     * @param DeploymentEvent $event
     *
     * @return TideEventWithMetadata
     */
    private function getDeploymentStartedEventWithMetadata(DeploymentEvent $event)
    {
        $buildStartedEvents = $this->eventStore->findByTideUuidAndTypeWithMetadata($event->getTideUuid(), DeploymentStarted::class);
        $matchingEvents = array_filter($buildStartedEvents, function (TideEventWithMetadata $eventWithMetadata) use ($event) {
            /** @var DeploymentStarted $deploymentStartedEvent */
            $deploymentStartedEvent = $eventWithMetadata->getTideEvent();

            return $deploymentStartedEvent->getDeployment()->getUuid() == $event->getDeployment()->getUuid();
        });

        if (0 === count($matchingEvents)) {
            return;
        }

        return current($matchingEvents);
    }
}
