<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

class RepositoryBranchQuery implements BranchQuery
{
    /**
     * @var array
     */
    private $queries;

    public function __construct(array $queries)
    {
        $this->queries = $queries;
    }

    /**
     * @return Branch[]
     */
    public function findBranches(FlatFlow $flow): array
    {
        if (isset($this->queries[get_class($flow->getRepository())])) {
            return $this->queries[get_class($flow->getRepository())]->findBranches($flow);
        }

        throw new \RuntimeException('No repository specific query found for flow');
    }
}
