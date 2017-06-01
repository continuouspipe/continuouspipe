<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\View\Storage\BranchViewStorage;
use Ramsey\Uuid\UuidInterface;

/**
 * This in-memory implementation is for testing purposes only.
 */
class InMemoryBranchViewStorage implements BranchViewStorage
{
    private $savedBranches = [];

    /**
     * @var BranchQuery
     */
    private $branchQuery;

    public function __construct(BranchQuery $branchQuery)
    {
        $this->branchQuery = $branchQuery;
    }

    public function save(UuidInterface $flowUuid)
    {
        $this->savedBranches = array_merge(
            $this->savedBranches,
            array_map(
                function ($branch) use ($flowUuid) {
                    return [$flowUuid, $branch];
                },
                $this->branchQuery->findBranches($flowUuid)
            )
        );
    }

    public function wasBranchSaved(UuidInterface $flowUUid, Branch $branch)
    {
        foreach($this->savedBranches as $savedBranch) {
            if ($flowUUid == $savedBranch[0] && $branch == $savedBranch[1]) {
                return true;
            }
        }
        return false;
    }
}