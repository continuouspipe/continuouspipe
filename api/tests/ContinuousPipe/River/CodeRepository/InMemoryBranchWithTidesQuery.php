<?php

namespace ContinuousPipe\River\CodeRepository;

class InMemoryBranchWithTidesQuery extends BranchWithTidesQuery
{
    private $innerQuery;

    public function __construct(InMemoryBranchQuery $innerQuery, InMemoryTidesForBranchQuery $tidesForBranchQuery)
    {
        parent::__construct($innerQuery, $tidesForBranchQuery);
        $this->innerQuery = $innerQuery;
    }

    public function addBranch($flow, $branch)
    {
        $this->innerQuery->addBranch($flow, $branch);
    }
}