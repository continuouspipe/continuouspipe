<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Flow;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;

interface FlowRepository
{
    /**
     * Save the given flow.
     *
     * @param Flow $flow
     *
     * @return Flow
     */
    public function save(Flow $flow);

    /**
     * Find flows by team.
     *
     * @param Team $team
     *
     * @return Flow[]
     */
    public function findByTeam(Team $team);

    /**
     * Delete the flow.
     *
     * @param Flow $flow
     */
    public function remove(Flow $flow);

    /**
     * Find a flow by its UUID.
     *
     * @param Uuid $uuid
     *
     * @throws FlowNotFound
     *
     * @return Flow
     */
    public function find(Uuid $uuid);
}
