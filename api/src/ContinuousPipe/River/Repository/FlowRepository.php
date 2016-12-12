<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\CodeRepository;
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
     * @deprecated Should use Events instead
     *
     * @return Flow
     */
    public function save(Flow $flow);

    /**
     * Find flows by team.
     *
     * @deprecated Need to use the `FlatFlowRepository` instead
     *
     * @param Team $team
     *
     * @return Flow[]
     */
    public function findByTeam(Team $team);

    /**
     * Delete the flow.
     *
     * @deprecated Need to use the `FlatFlowRepository` instead
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

    /**
     * @deprecated Need to use the `FlatFlowRepository` instead
     *
     * @param CodeRepository $codeRepository
     *
     * @return Flow[]
     */
    public function findByCodeRepository(CodeRepository $codeRepository);
}
