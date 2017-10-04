<?php

namespace ContinuousPipe\Builder\Reporting;

use ContinuousPipe\Builder\Aggregate\Event\BuildCreated;
use ContinuousPipe\Builder\Aggregate\Event\BuildFinished;
use ContinuousPipe\Builder\Aggregate\Event\BuildStarted;
use ContinuousPipe\Builder\Aggregate\FromEvents\EventStream;
use ContinuousPipe\Events\EventStore\EventStore;
use ContinuousPipe\Events\EventStore\EventStoreException;
use ContinuousPipe\Events\EventStore\EventWithMetadata;
use Psr\Log\LoggerInterface;

class AddsDurationFromEvents implements ReportBuilder
{
    /**
     * @var ReportBuilder
     */
    private $decoratedBuilder;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ReportBuilder $decoratedBuilder, EventStore $eventStore, LoggerInterface $logger)
    {
        $this->decoratedBuilder = $decoratedBuilder;
        $this->eventStore = $eventStore;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $buildIdentifier): array
    {
        $report = $this->decoratedBuilder->build($buildIdentifier);

        try {
            $events = $this->eventStore->readWithMetadata(EventStream::fromBuildIdentifier($buildIdentifier));
        } catch (EventStoreException $e) {
            $this->logger->warning('Can\'t add the durations on the build report', [
                'exception' => $e,
            ]);

            return $report;
        }

        $report['duration'] = [];

        $buildCreated = $this->getDateTimeOfEventOfType($events, BuildCreated::class);
        $buildStarted = $this->getDateTimeOfEventOfType($events, BuildStarted::class);
        $buildFinished = $report['status'] == 'success'
            ? $this->getDateTimeOfEventOfType($events, BuildFinished::class)
            : $this->getDateTimeOfEventOfType($events, BuildFinished::class)
        ;

        if ($buildCreated !== null && $buildStarted !== null) {
            $report['duration']['pending'] = $buildStarted->getTimestamp() - $buildCreated->getTimestamp();
        }
        if ($buildStarted !== null && $buildFinished !== null) {
            $report['duration']['running'] = $buildFinished->getTimestamp() - $buildStarted->getTimestamp();
        }
        if ($buildCreated !== null && $buildFinished !== null) {
            $report['duration']['total'] = $buildFinished->getTimestamp() - $buildCreated->getTimestamp();
        }

        return $report;
    }

    /**
     * @param EventWithMetadata[] $events
     * @param string $class
     *
     * @return \DateTimeInterface|null
     */
    private function getDateTimeOfEventOfType(array $events, string $class)
    {
        foreach ($events as $event) {
            if (get_class($event->getEvent()) == $class) {
                return $event->getMetadata()->getDateTime();
            }
        }

        return null;
    }
}
