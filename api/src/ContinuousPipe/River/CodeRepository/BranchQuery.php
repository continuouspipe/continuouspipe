<?php

namespace ContinuousPipe\River\CodeRepository;

use Ramsey\Uuid\UuidInterface;

interface BranchQuery
{
    /**
     * @return Branch[]
     */
    public function findBranches(UuidInterface $flowUuid): array;
}