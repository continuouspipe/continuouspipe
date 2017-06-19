<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface BranchQuery
{
    /**
     * @return Branch[]
     */
    public function findBranches(FlatFlow $flow): array;
}
