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
     * @param string $visibility
     *
     * @throws QuayException
     *
     * @return Repository
     */
    public function createRepository(string $name, string $visibility) : Repository;

    /**
     * Allow a user to access a repository.
     *
     * @param string $username
     * @param string $repositoryName Name of the repository. Example: continuouspipe-flex/flow-0000
     *
     * @throws QuayException
     */
    public function allowUserToAccessRepository(string $username, string $repositoryName);

    /**
     * @param string $repositoryName Name of the repository. Example: continuouspipe-flex/flow-0000
     * @param string $visibility     `public` or `private`
     *
     * @return QuayException
     */
    public function changeVisibility(string $repositoryName, string $visibility);
}
