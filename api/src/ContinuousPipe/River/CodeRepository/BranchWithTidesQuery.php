<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\UuidInterface;

class BranchWithTidesQuery implements BranchQuery
{
    const MAX_TIDES = 5;
    private $innerQuery;
    private $tideRepository;

    public function __construct(BranchQuery $innerQuery, TideRepository $tideRepository)
    {
        $this->innerQuery = $innerQuery;
        $this->tideRepository = $tideRepository;
    }

    public function findBranches(UuidInterface $flowUuid): array
    {

        return array_map(function(Branch $branch) use ($flowUuid) {
            return $branch->withTides($this->tideRepository->findByBranch($flowUuid, (string) $branch, self::MAX_TIDES));
        },
            $this->innerQuery->findBranches($flowUuid)
        );
    }

}