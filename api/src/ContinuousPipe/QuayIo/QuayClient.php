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
     * Allow a robot to access a repository.
     *
     * @param string $robotName
     * @param string $repositoryName
     *
     * @throws QuayException
     */
    public function allowRobotToAccessRepository(string $robotName, string $repositoryName) : void;
}
