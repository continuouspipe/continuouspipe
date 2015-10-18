<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Flow;
use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;

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
     * Find flows by user.
     *
     * @param User $user
     *
     * @return Flow[]
     */
    public function findByUser(User $user);

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
