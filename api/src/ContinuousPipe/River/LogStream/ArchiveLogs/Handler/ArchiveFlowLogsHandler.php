<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Handler;

use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\LogStream\ArchiveLogs\Command\ArchiveFlowLogsCommand;
use ContinuousPipe\River\LogStream\ArchiveLogs\Event\TideLogsArchived;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use LogStream\Client;
use LogStream\Tree\TreeLog;
use Rhumsaa\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;

class ArchiveFlowLogsHandler
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var Client
     */
    private $logStreamClient;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param TideRepository $tideRepository
     * @param Client         $logStreamClient
     * @param EventStore     $eventStore
     * @param MessageBus     $eventBus
     */
    public function __construct(TideRepository $tideRepository, Client $logStreamClient, EventStore $eventStore, MessageBus $eventBus)
    {
        $this->tideRepository = $tideRepository;
        $this->logStreamClient = $logStreamClient;
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
    }

    /**
     * @param ArchiveFlowLogsCommand $command
     */
    public function handle(ArchiveFlowLogsCommand $command)
    {
        $tides = $this->getArchivableTides($command->getFlowUuid());

        foreach ($tides as $tide) {
            try {
                $this->logStreamClient->archive(TreeLog::fromId($tide->getLogId()));
                $this->eventBus->handle(new TideLogsArchived($tide->getUuid()));
            } catch (Client\ClientException $e) {
                if ($e->getMessage() == 'Found status 404') {
                    $this->eventBus->handle(new TideLogsArchived($tide->getUuid()));
                }
            }
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
