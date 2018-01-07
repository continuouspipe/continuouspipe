<?php

namespace ContinuousPipe\Security\Account;

class BitBucketAccount extends Account
{
    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @param string      $uuid
     * @param string      $username
     * @param string      $identifier
     * @param string      $email
     * @param string      $refreshToken
     * @param string|null $name
     * @param string|null $pictureUrl
     */
    public function __construct(string $uuid, string $username, string $identifier, string $email, string $refreshToken, string $name = null, string $pictureUrl = null)
    {
        parent::__construct($uuid, $username, $identifier, $email, $name, $pictureUrl);

        $this->refreshToken = $refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return 'bitbucket';
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
