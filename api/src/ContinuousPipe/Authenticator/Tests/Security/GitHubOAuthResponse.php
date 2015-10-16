<?php

namespace ContinuousPipe\Authenticator\Tests\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class GitHubOAuthResponse extends AbstractUserResponse
{
    /**
     * @var string
     */
    private $username;

    /**
     * @param string     $username
     * @param OAuthToken $token
     */
    public function __construct($username, OAuthToken $token = null)
    {
        $this->username = $username;
        $this->oAuthToken = $token ?: new OAuthToken('token');
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return [
            'login' => $this->username,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRealName()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->username;
    }
}
