<?php

namespace ContinuousPipe\River\CodeRepository;

class BranchWithTidesQuery implements BranchQuery
{
    private $innerQuery;
    private $tidesForBranchQuery;

    public function __construct(BranchQuery $innerQuery, TidesForBranchQuery $tidesForBranchQuery)
    {
        $this->innerQuery = $innerQuery;
        $this->tidesForBranchQuery = $tidesForBranchQuery;
    }

    public function findBranches($flow): array
    {

        return array_map(function(Branch $branch) {
            return $branch->withTides($this->tidesForBranchQuery->findLatestTides($branch));
        },
            $this->innerQuery->findBranches($flow)
        );
    }

}