<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

class InMemoryBranchQuery implements BranchQuery
{

    private $branches = [];
    /**
     * @var BranchQuery
     */
    private $innerQuery;
    private $onlyInMemory = true;

    public function __construct(BranchQuery $innerQuery)
    {
        $this->innerQuery = $innerQuery;
    }

    public function findBranches(FlatFlow $flow): array
    {
        if(isset($this->branches[(string) $flow->getUuid()])) {
            return $this->branches[(string) $flow->getUuid()];
        }

        if ($this->onlyInMemory) {
            return [];
        }
        
        return $this->innerQuery->findBranches($flow);
    }

    public function addBranch($flowUuid, $branch)
    {
        $toAdd = [new Branch($branch)];

        $this->branches[$flowUuid] = isset($this->branches[$flowUuid]) ? array_merge($this->branches[$flowUuid], $toAdd) : $toAdd;
    }

    public function onlyInMemory()
    {
        $this->onlyInMemory = true;
    }

    public function notOnlyInMemory()
    {
        $this->onlyInMemory = false;
    }


}