<?php

namespace ContinuousPipe\Security\Credentials;

class GitHubToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     * @param string $accessToken
     */
    public function __construct($identifier, $accessToken)
    {
        $this->identifier = $identifier;
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
}
