<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\Builder\Repository;

class StartTideCommand
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
