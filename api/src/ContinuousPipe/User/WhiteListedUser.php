<?php

namespace ContinuousPipe\User;

class WhiteListedUser
{
    /**
     * @var string
     */
    private $gitHubUsername;

    /**
     * @param string $gitHubUsername
     */
    public function __construct($gitHubUsername = null)
    {
        $this->gitHubUsername = $gitHubUsername;
    }

    /**
     * @param string $gitHubUsername
     */
    public function setGitHubUsername($gitHubUsername)
    {
        $this->gitHubUsername = $gitHubUsername;
    }

    /**
     * @return string
     */
    public function getGitHubUsername()
    {
        return $this->gitHubUsername;
    }
}
