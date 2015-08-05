<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Flow;
use ContinuousPipe\User\User;

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
     * @param string $identifier
     *
     * @return Flow
     */
    public function findOneByRepositoryIdentifier($identifier);

    /**
     * Find flows by user.
     *
     * @param User $user
     *
     * @return Flow[]
     */
    public function findByUser(User $user);
}
