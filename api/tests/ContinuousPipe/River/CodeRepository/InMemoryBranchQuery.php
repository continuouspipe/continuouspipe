<?php

namespace ContinuousPipe\River\CodeRepository;

use Ramsey\Uuid\UuidInterface;

class InMemoryBranchQuery implements BranchQuery
{
    private $branches = [];

    public function findBranches(UuidInterface $flowUuid): array
    {
        return isset($this->branches[(string) $flowUuid]) ? $this->branches[(string) $flowUuid] : [];
    }

    public function addBranch($flow, $branch)
    {
        $toAdd = [new Branch($branch)];

        $this->branches[$flow] = isset($this->branches[$flow]) ? array_merge($this->branches[$flow], $toAdd) : $toAdd;
    }
}