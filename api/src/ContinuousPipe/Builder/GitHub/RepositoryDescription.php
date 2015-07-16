<?php

namespace ContinuousPipe\Builder\GitHub;

class RepositoryDescription
{
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $repository;

    /**
     * @param string $username
     * @param string $repository
     */
    public function __construct($username, $repository)
    {
        $this->username = $username;
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }
}