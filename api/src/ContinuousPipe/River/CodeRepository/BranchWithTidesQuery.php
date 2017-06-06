<?php

namespace ContinuousPipe\River\CodeRepository;

use Ramsey\Uuid\UuidInterface;

class BranchWithTidesQuery implements BranchQuery
{
    private $innerQuery;
    private $tidesForBranchQuery;

    public function __construct(BranchQuery $innerQuery, TidesForBranchQuery $tidesForBranchQuery)
    {
        $this->innerQuery = $innerQuery;
        $this->tidesForBranchQuery = $tidesForBranchQuery;
    }

    public function findBranches(UuidInterface $flowUuid): array
    {

        return array_map(function(Branch $branch) {
            return $branch->withTides($this->tidesForBranchQuery->findLatestTides($branch));
        },
            $this->innerQuery->findBranches($flowUuid)
        );
    }

}