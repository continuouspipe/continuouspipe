<?php

namespace ContinuousPipe\River\Migrations\GetEventStoreToSQLStore;

use ContinuousPipe\River\EventStore\EventStore;
use ContinuousPipe\River\Flow\EventStream;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\Security\Team\TeamRepository;

class FlowEventsMigrator
{
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @var EventStore
     */
    private $source;

    /**
     * @var EventStore
     */
    private $target;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(
        TeamRepository $teamRepository,
        FlatFlowRepository $flatFlowRepository,
        EventStore $source,
        EventStore $target
    ) {
        $this->teamRepository = $teamRepository;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->source = $source;
        $this->target = $target;
    }

    public function migrate() : int
    {
        $migrated = 0;

        foreach ($this->teamRepository->findAll() as $team) {
            foreach ($this->flatFlowRepository->findByTeam($team) as $flow) {
                $migrated += $this->migrateFlow($flow);
            }
        }

        return $migrated;
    }

    private function migrateFlow(FlatFlow $flow) : int
    {
        $stream = EventStream::fromUuid($flow->getUuid());

        // Do not migrate if the stream already exists
        if (!empty($this->target->read($stream))) {
            return 0;
        }

        $existingEvents = $this->source->read($stream);
        foreach ($existingEvents as $existingEvent) {
            $this->target->store($stream, $existingEvent);
        }

        return count($existingEvents);
    }
}
