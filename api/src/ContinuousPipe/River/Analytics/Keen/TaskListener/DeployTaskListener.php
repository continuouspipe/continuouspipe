<?php

namespace ContinuousPipe\River\Analytics\Keen\TaskListener;

use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;
use ContinuousPipe\River\Analytics\Keen\Normalizer\DeploymentNormalizer;
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
     * @var KeenClient
     */
    private $keenClient;

    /**
     * @param KeenClient      $keenClient
     * @param EventStore      $eventStore
     * @param LoggerInterface $logger
     */
    public function __construct(KeenClient $keenClient, EventStore $eventStore, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
        $this->keenClient = $keenClient;
    }

    public function notify(DeploymentEvent $event)
    {
        $deployment = $event->getDeployment();

        if (null === ($deploymentStarted = $this->getDeploymentStartedEventWithMetadata($event))) {
            $this->logger->critical('Unable to find deployment started event for this deploy task', [
                'eventClass' => get_class($event),
                'status' => $deployment->getStatus(),
                'deploymentUuid' => (string) $deployment->getUuid(),
                'tideUuid' => (string) $event->getTideUuid(),
            ]);

            return;
        }

        $startedAt = $deploymentStarted->getDateTime();
        $finishedAt = new \DateTime();

        $this->keenClient->addEvent('deployments', array_merge(
            (new DeploymentNormalizer())->normalize($deployment),
            [
                'tide' => [
                    'uuid' => (string) $event->getTideUuid(),
                ],
                'timing' => [
                    'started_at' => $startedAt->format(\DateTime::ISO8601),
                    'finished_at' => $finishedAt->format(\DateTime::ISO8601),
                ],
                'duration' => [
                    'total' => $finishedAt->getTimestamp() - $startedAt->getTimestamp(),
                ],
            ]
        ));
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
