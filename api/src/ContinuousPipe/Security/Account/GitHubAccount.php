<?php

namespace ContinuousPipe\Security\Account;

class GitHubAccount extends Account
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param string      $uuid
     * @param string      $identifier
     * @param string      $login
     * @param string      $token
     * @param string|null $email
     * @param string|null $name
     * @param string|null $pictureUrl
     */
    public function __construct(string $uuid, string $identifier, string $login, string $token, string $email = null, string $name = null, string $pictureUrl = null)
    {
        parent::__construct($uuid, $login, $identifier, $email, $name, $pictureUrl);

        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return 'github';
    }
}
