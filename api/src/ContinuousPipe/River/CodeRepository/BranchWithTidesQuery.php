<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\View\TideRepository;

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

    public function findBranches(FlatFlow $flow): array
    {

        return array_map(
            function (Branch $branch) use ($flow) {
                return $branch->withTides(
                    $this->tideRepository->findByBranch($flow->getUuid(), (string) $branch, self::MAX_TIDES)
                );
            },
            $this->innerQuery->findBranches($flow)
        );
    }

}