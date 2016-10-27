<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Handler;

use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveFlowLogsCommand;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveTideCommand;
use ContinuousPipe\River\LogStream\ArchiveLogs\Event\TideLogsArchived;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class ArchiveFlowLogsHandler
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param TideRepository $tideRepository
     * @param EventStore     $eventStore
     * @param MessageBus     $commandBus
     */
    public function __construct(TideRepository $tideRepository, EventStore $eventStore, MessageBus $commandBus)
    {
        $this->tideRepository = $tideRepository;
        $this->eventStore = $eventStore;
        $this->commandBus = $commandBus;
    }

    /**
     * @param ArchiveFlowLogsCommand $command
     */
    public function handle(ArchiveFlowLogsCommand $command)
    {
        $tides = $this->getArchivableTides($command->getFlowUuid());

        foreach ($tides as $tide) {
            $this->commandBus->handle(new ArchiveTideCommand($tide->getUuid(), $tide->getLogId()));
        }
    }

    /**
     * @param Uuid $flowUuid
     *
     * @return Tide[]
     */
    private function getArchivableTides(Uuid $flowUuid)
    {
        $tides = $this->tideRepository->findByFlowUuid($flowUuid)->toArray();

        // Only archive tides finished before yesterday
        $yesterday = (new \DateTime())->sub(new \DateInterval('P1D'));
        $tides = array_filter($tides, function (Tide $tide) use ($yesterday) {
            return in_array($tide->getStatus(), [Tide::STATUS_FAILURE, Tide::STATUS_SUCCESS])
                && $tide->getFinishDate() < $yesterday;
        });

        // Only archive non-archived tides
        $tides = array_filter($tides, function (Tide $tide) {
            $tideArchivedEvents = $this->eventStore->findByTideUuidAndType($tide->getUuid(), TideLogsArchived::class);

            return count($tideArchivedEvents) == 0;
        });

        return $tides;
    }
}
