<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\UuidInterface;

interface FlatFlowRepository
{
    /**
     * @param UuidInterface $uuid
     *
     * @throws FlowNotFound
     *
     * @return FlatFlow
     */
    public function find(UuidInterface $uuid);

    /**
     * @param Team $team
     *
     * @return FlatFlow[]
     */
    public function findByTeam(Team $team) : array;

    /**
     * @param CodeRepository $repository
     *
     * @return FlatFlow[]
     */
    public function findByCodeRepository(CodeRepository $repository) : array;

    /**
     * @param UuidInterface $uuid
     */
    public function remove(UuidInterface $uuid);

    /**
     * @param FlatFlow $flow
     */
    public function save(FlatFlow $flow);
}
