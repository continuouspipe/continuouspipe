<?php

namespace ContinuousPipe\River\Flow\Migrations\ToEventSourced;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\TeamRepository;
use SimpleBus\Message\Bus\MessageBus;

class Migrator
{
    /**
     * @var FlowRepository
     */
    private $legacyFlowRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var FlowRepository
     */
    private $eventBasedRepository;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param TeamRepository $teamRepository
     * @param FlowRepository $legacyFlowRepository
     * @param FlowRepository $eventBasedRepository
     * @param MessageBus     $eventBus
     */
    public function __construct(TeamRepository $teamRepository, FlowRepository $legacyFlowRepository, FlowRepository $eventBasedRepository, MessageBus $eventBus)
    {
        $this->legacyFlowRepository = $legacyFlowRepository;
        $this->teamRepository = $teamRepository;
        $this->eventBasedRepository = $eventBasedRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * Migrate the non-event sourced flows to be event-sourced.
     *
     * It returns the number of migrated flows.
     *
     * @return int
     */
    public function migrate() : int
    {
        $migrated = 0;

        foreach ($this->findFlows() as $flow) {
            try {
                $this->eventBasedRepository->find($flow->getUuid());
            } catch (FlowNotFound $e) {
                array_map(function ($event) {
                    $this->eventBus->handle($event);
                }, [
                    new Flow\Event\FlowCreated(
                        $flow->getUuid(),
                        $flow->getTeam(),
                        $flow->getUser(),
                        $flow->getCodeRepository()
                    ),
                    new Flow\Event\FlowConfigurationUpdated(
                        $flow->getUuid(),
                        $flow->getConfiguration()
                    ),
                ]);

                ++$migrated;
            }
        }

        return $migrated;
    }

    /**
     * @return Flow[]|\Generator
     */
    private function findFlows()
    {
        foreach ($this->teamRepository->findAll() as $team) {
            foreach ($this->legacyFlowRepository->findByTeam($team) as $flow) {
                yield $flow;
            }
        }
    }
}
