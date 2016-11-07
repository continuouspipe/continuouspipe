<?php

namespace ContinuousPipe\Authenticator\Tests\Security;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class GitHubOAuthResponse extends AbstractUserResponse
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var null|string
     */
    private $email;

    /**
     * @param string              $username
     * @param OAuthToken          $token
     * @param string              $email
     * @param GitHubResourceOwner $gitHubResourceOwner
     */
    public function __construct($username, OAuthToken $token = null, $email = null, GitHubResourceOwner $gitHubResourceOwner = null)
    {
        $this->username = $username;
        $this->oAuthToken = $token ?: new OAuthToken('token');
        $this->email = $email;
        $this->resourceOwner = $gitHubResourceOwner;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return [
            'login' => $this->username,
            'id' => '1234',
            'email' => $this->email ?: $this->username.'@example.com',
            'name' => $this->username,
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
        return $this->email ?: $this->username;
    }
}
