<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use Ramsey\Uuid\UuidInterface;

class PinnedBranchQuery implements BranchQuery
{
    private $innerQuery;
    private $flatFlowRepository;

    public function __construct(BranchQuery $innerQuery, FlatFlowRepository $flatFlowRepository)
    {
        $this->innerQuery = $innerQuery;
        $this->flatFlowRepository = $flatFlowRepository;
    }

    public function findBranches(UuidInterface $flowUuid): array
    {
        $flow = $this->flatFlowRepository->find($flowUuid);
        return array_map(function(Branch $branch) use ($flow) {
            return $branch->pinned($flow->isBranchPinned($branch));
        },
            $this->innerQuery->findBranches($flowUuid)
        );
    }

}