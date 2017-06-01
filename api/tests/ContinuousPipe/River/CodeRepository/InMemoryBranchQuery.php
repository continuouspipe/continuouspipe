<?php

namespace ContinuousPipe\River\CodeRepository;

class InMemoryBranchQuery implements BranchQuery
{
    private $branches = [];

    public function findBranches($flow): array
    {
        return isset($this->branches[(string) $flow]) ? $this->branches[(string) $flow] : [];
    }

    public function addBranch($flow, $branch)
    {
        $toAdd = [new Branch($branch)];

        $this->branches[$flow] = isset($this->branches[$flow]) ? array_merge($this->branches[$flow], $toAdd) : $toAdd;
    }
}