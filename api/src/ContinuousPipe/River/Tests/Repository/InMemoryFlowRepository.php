<?php

namespace ContinuousPipe\River\Tests\Repository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;

class InMemoryFlowRepository implements FlowRepository
{
    private $flowsByUuid = [];
    private $flowsByTeam = [];

    /**
     * {@inheritdoc}
     */
    public function save(Flow $flow)
    {
        $this->flowsByUuid[(string) $flow->getUuid()] = $flow;

        $teamSlug = $flow->getTeam()->getSlug();
        if (!array_key_exists($teamSlug, $this->flowsByTeam)) {
            $this->flowsByTeam[$teamSlug] = [];
        }
        $this->flowsByTeam[$teamSlug][] = $flow;

        return $flow;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team)
    {
        $teamSlug = $team->getSlug();
        if (!array_key_exists($teamSlug, $this->flowsByTeam)) {
            return [];
        }

        return $this->flowsByTeam[$teamSlug];
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Flow $flow)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->flowsByUuid)) {
            throw new FlowNotFound();
        }

        return $this->flowsByUuid[(string) $uuid];
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $codeRepository)
    {
        $matchingFlows = array_filter($this->flowsByUuid, function (Flow $flow) use ($codeRepository) {
            return $flow->getCodeRepository()->getIdentifier() == $codeRepository->getIdentifier();
        });

        return array_values($matchingFlows);
    }
}
