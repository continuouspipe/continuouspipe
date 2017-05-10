<?php

namespace ContinuousPipe\River\Analytics\Logitio\TaskListener;

use ContinuousPipe\Builder\Client\BuilderBuild;
use ContinuousPipe\River\Analytics\Logitio\Client\LogitioClient;
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
     * @var LogitioClient
     */
    private $logitioClient;

    public function __construct(LogitioClient $logitioClient, EventStore $eventStore, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
        $this->logitioClient = $logitioClient;
    }

    /**
     * @param BuildEvent $event
     */
    public function notify(BuildEvent $event)
    {
        $build = $event->getBuild();

        if ($build->getStatus() == BuilderBuild::STATUS_RUNNING) {
            return;
        }

        if (null === ($buildStarted = $this->getBuildStartedEventWithMetadata($event))) {
            $this->logger->critical('Unable to find build started event for this build', [
                'eventClass' => get_class($event),
                'status' => $build->getStatus(),
                'buildUuid' => (string) $event->getBuild()->getUuid(),
                'tideUuid' => (string) $event->getTideUuid(),
            ]);

            return;
        }

        if ($build->getStatus() == BuilderBuild::STATUS_SUCCESS) {
            $statusCode = 201;
        } elseif ($build->getStatus() == BuilderBuild::STATUS_ERROR) {
            $statusCode = 502;
        } else {
            $statusCode = 501;
        }

        $startedAt = $buildStarted->getDateTime();
        $finishedAt = new \DateTime();

        $this->logitioClient->addEvent('BuildLog', [
            'build_uuid' => (string) $event->getBuild()->getUuid(),
            'tide_uuid' => (string) $event->getTideUuid(),
            'status' => [
                //'debug_identifier' => ???
                //'reason' => ???
                'code' => $statusCode
            ],
            'duration' => $finishedAt->getTimestamp() - $startedAt->getTimestamp(),
            'step_count' => count($build->getRequest()->getSteps()),
            //'username' => $tide->getUser()->getUsername(),
            //'project' => $tide->getTeam()->getName(),
            //'flow_name' => ???
            'timestamp' => time(),
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
