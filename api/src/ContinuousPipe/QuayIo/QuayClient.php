<?php

namespace ContinuousPipe\QuayIo;

interface QuayClient
{
    /**
     * Create a new robot account.
     *
     * @param string $name
     *
     * @throws QuayException
     *
     * @return RobotAccount
     */
    public function createRobotAccount(string $name) : RobotAccount;

    /**
     * Create a new repository.
     *
     * @param string $name
     *
     * @throws QuayException
     *
     * @return Repository
     */
    public function createRepository(string $name) : Repository;

    /**
     * Allow a user to access a repository.
     *
     * @param string $username
     * @param string $repositoryName
     *
     * @throws QuayException
     */
    public function allowUserToAccessRepository(string $username, string $repositoryName);
}
