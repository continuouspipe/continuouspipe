<?php

namespace ContinuousPipe\River\Analytics\Keen\TaskListener;

use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;
use ContinuousPipe\River\Analytics\Keen\Normalizer\BuildRequestNormalizer;
use ContinuousPipe\River\Event\TideEventWithMetadata;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Task\Build\Event\BuildEvent;
use ContinuousPipe\River\Task\Build\Event\BuildStarted;
use Psr\Log\LoggerInterface;

class BuildTaskListener
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

    /**
     * @param BuildEvent $event
     */
    public function notify(BuildEvent $event)
    {
        $build = $event->getBuild();

        if (null === ($buildStarted = $this->getBuildStartedEventWithMetadata($event))) {
            $this->logger->critical('Unable to find build started event for this build', [
                'eventClass' => get_class($event),
                'status' => $build->getStatus(),
                'buildUuid' => (string) $event->getBuild()->getUuid(),
                'tideUuid' => (string) $event->getTideUuid(),
            ]);

            return;
        }

        $startedAt = $buildStarted->getDateTime();
        $finishedAt = new \DateTime();

        $this->keenClient->addEvent('builds', [
            'uuid' => (string) $event->getBuild()->getUuid(),
            'status' => $build->getStatus(),
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
            'build' => (new BuildRequestNormalizer())->normalize($build->getRequest()),
        ]);
    }

    /**
     * @param BuildEvent $event
     *
     * @return TideEventWithMetadata
     */
    private function getBuildStartedEventWithMetadata(BuildEvent $event)
    {
        $buildStartedEvents = $this->eventStore->findByTideUuidAndTypeWithMetadata($event->getTideUuid(), BuildStarted::class);
        $matchingEvents = array_filter($buildStartedEvents, function (TideEventWithMetadata $eventWithMetadata) use ($event) {
            /** @var BuildStarted $buildStartedEvent */
            $buildStartedEvent = $eventWithMetadata->getTideEvent();

            return $buildStartedEvent->getBuild()->getUuid() == $event->getBuild()->getUuid();
        });

        if (0 === count($matchingEvents)) {
            return;
        }

        return current($matchingEvents);
    }
}
