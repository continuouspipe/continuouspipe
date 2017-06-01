<?php

namespace ContinuousPipe\River\CodeRepository;

interface BranchQuery
{
    /**
     * @return Branch[]
     */
    public function findBranches($flow): array;
}