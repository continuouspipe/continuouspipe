<?php

namespace ContinuousPipe\River\Analytics\Keen;

use ContinuousPipe\River\Analytics\Keen\Client\KeenClient;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideStarted;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\EventBus\EventStore;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class TideReportListener
{
    /**
     * @var KeenClient
     */
    private $keenClient;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param KeenClient      $keenClient
     * @param EventStore      $eventStore
     * @param LoggerInterface $logger
     */
    public function __construct(KeenClient $keenClient, EventStore $eventStore, LoggerInterface $logger)
    {
        $this->keenClient = $keenClient;
        $this->eventStore = $eventStore;
        $this->logger = $logger;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        if ($event instanceof TideSuccessful) {
            $status = 'success';
        } elseif ($event instanceof TideFailed) {
            $status = 'failure';
        } else {
            $status = 'unknown';
        }

        $tideCreatedEvents = $this->eventStore->findByTideUuidAndType($event->getTideUuid(), TideCreated::class);
        if (count($tideCreatedEvents) == 0) {
            $this->logger->critical('No tide created event found, unable to create keen report', [
                'tideUuid' => $event->getTideUuid(),
                'status' => $status,
            ]);

            return;
        }

        /** @var TideCreated $tideCreatedEvent */
        $tideCreatedEvent = $tideCreatedEvents[0];
        $context = $tideCreatedEvent->getTideContext();

        $createdAt = $this->getFirstEventDateTime($event->getTideUuid(), TideCreated::class) ?: new \DateTime();
        $startedAt = $this->getFirstEventDateTime($event->getTideUuid(), TideStarted::class) ?: new \DateTime();
        $failedAt = $this->getFirstEventDateTime($event->getTideUuid(), TideFailed::class);
        $succeedAt = $this->getFirstEventDateTime($event->getTideUuid(), TideSuccessful::class);
        $finishedAt = $succeedAt ?: $failedAt ?: new \DateTime();

        $this->keenClient->addEvent('tides', [
            'uuid' => (string) $context->getTideUuid(),
            'status' => $status,
            'timing' => [
                'created_at' => $createdAt->format(\DateTime::ISO8601),
                'started_at' => $startedAt->format(\DateTime::ISO8601),
                'failed_at' => $failedAt !== null ? $failedAt->format(\DateTime::ISO8601) : null,
                'succeed_at' => $succeedAt !== null ? $succeedAt->format(\DateTime::ISO8601) : null,
                'finished_at' => $finishedAt->format(\DateTime::ISO8601),
            ],
            'duration' => [
                'total' => $finishedAt->getTimestamp() - $createdAt->getTimeStamp(),
                'queueing' => $startedAt->getTimestamp() - $createdAt->getTimestamp(),
                'execution' => $finishedAt->getTimestamp() - $startedAt->getTimestamp(),
            ],
            'flow' => [
                'uuid' => (string) $context->getFlowUuid(),
            ],
            'team' => [
                'slug' => $context->getTeam()->getSlug(),
            ],
            'user' => [
                'username' => $context->getUser()->getUsername(),
                'email' => $context->getUser()->getEmail(),
            ],
            'code_reference' => [
                'sha1' => $context->getCodeReference()->getCommitSha(),
                'branch' => $context->getCodeReference()->getBranch(),
            ],
            'repository' => [
                'identifier' => $context->getCodeRepository()->getIdentifier(),
                'address' => $context->getCodeRepository()->getAddress(),
            ],
            'configuration' => $context->getConfiguration(),
        ]);
    }

    /**
     * @param Uuid   $tideUuid
     * @param string $className
     *
     * @return \DateTime|null
     */
    private function getFirstEventDateTime(Uuid $tideUuid, $className)
    {
        $events = $this->eventStore->findByTideUuidAndTypeWithMetadata($tideUuid, $className);
        if (count($events) == 0) {
            return;
        }

        return $events[0]->getDateTime();
    }
}
