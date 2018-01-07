<?php

namespace ContinuousPipe\QuayIo;

use Exception;

class RepositoryAlreadyExists extends QuayException
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Exception $previous = null)
    {
        parent::__construct('Repository already exists', 1, $previous);
    }

    /**
     * @param Repository $repository
     *
     * @return $this
     */
    public function withRepository(Repository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return Repository|null
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
