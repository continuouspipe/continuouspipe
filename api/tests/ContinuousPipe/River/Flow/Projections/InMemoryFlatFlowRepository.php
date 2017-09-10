<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\UuidInterface;

class InMemoryFlatFlowRepository implements FlatFlowRepository
{
    private $flowsByUuid = [];
    private $flowsByTeam = [];

    /**
     * {@inheritdoc}
     */
    public function save(FlatFlow $flow)
    {
        $this->flowsByUuid[(string) $flow->getUuid()] = $flow;

        $teamSlug = $flow->getTeam()->getSlug();
        if (!array_key_exists($teamSlug, $this->flowsByTeam)) {
            $this->flowsByTeam[$teamSlug] = [];
        }
        $this->flowsByTeam[$teamSlug][$flow->getUuid()->toString()] = $flow;

        return $flow;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTeam(Team $team) : array
    {
        $teamSlug = $team->getSlug();
        if (!array_key_exists($teamSlug, $this->flowsByTeam)) {
            return [];
        }

        return array_values($this->flowsByTeam[$teamSlug]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(UuidInterface $flow)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->flowsByUuid)) {
            throw new FlowNotFound();
        }

        return $this->flowsByUuid[(string) $uuid];
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeRepository(CodeRepository $codeRepository) : array
    {
        $matchingFlows = array_filter($this->flowsByUuid, function (FlatFlow $flow) use ($codeRepository) {
            return $flow->getRepository()->getIdentifier() == $codeRepository->getIdentifier();
        });

        return array_values($matchingFlows);
    }
}
