<?php

namespace ContinuousPipe\Security\Account;

class GoogleAccount extends Account
{
    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * Base64-encoded service account.
     *
     * @var string|null
     */
    private $serviceAccount;

    public function __construct(
        string $uuid,
        string $identifier,
        string $email,
        string $refreshToken = null,
        string $name = null,
        string $pictureUrl = null,
        string $serviceAccount = null
    ) {
        parent::__construct($uuid, $email, $identifier, $email, $name, $pictureUrl);

        $this->refreshToken = $refreshToken;
        $this->serviceAccount = $serviceAccount;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return 'google';
    }

    /**
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return null|string
     */
    public function getServiceAccount()
    {
        return $this->serviceAccount;
    }
}
