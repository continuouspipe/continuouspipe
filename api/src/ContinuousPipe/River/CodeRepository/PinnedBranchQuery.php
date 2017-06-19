<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

class PinnedBranchQuery implements BranchQuery
{
    private $innerQuery;

    public function __construct(BranchQuery $innerQuery)
    {
        $this->innerQuery = $innerQuery;
    }

    public function findBranches(FlatFlow $flow): array
    {
        return array_map(
            function (Branch $branch) use ($flow) {
                return $flow->isBranchPinned($branch) ? $branch->pinned() : $branch->unpinned();
            },
            $this->innerQuery->findBranches($flow)
        );
    }
}
