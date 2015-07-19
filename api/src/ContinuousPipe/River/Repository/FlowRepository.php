<?php

namespace ContinuousPipe\River\Repository;

use ContinuousPipe\River\Flow;

interface FlowRepository
{
    /**
     * Save the given flow.
     *
     * @param Flow $flow
     * @return Flow
     */
    public function save(Flow $flow);

    /**
     * @param string $identifier
     * @return Flow
     */
    public function findOneByRepositoryIdentifier($identifier);
}
