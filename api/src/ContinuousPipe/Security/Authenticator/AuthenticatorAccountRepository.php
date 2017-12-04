<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountNotFound;
use ContinuousPipe\Security\Account\AccountRepository;
use Ramsey\Uuid\Uuid;

class AuthenticatorAccountRepository implements AccountRepository
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @param AuthenticatorClient $authenticatorClient
     */
    public function __construct(AuthenticatorClient $authenticatorClient)
    {
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $uuid)
    {
        return $this->authenticatorClient->findAccountByUuid(Uuid::fromString($uuid));
    }

    /**
     * {@inheritdoc}
     */
    public function findByUsername(string $username)
    {
        return $this->authenticatorClient->findAccountsByUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function link(string $username, Account $account)
    {
        throw new \RuntimeException('This is not supported by the API client implementation');
    }

    /**
     * {@inheritdoc}
     */
    public function unlink(string $username, Account $account)
    {
        throw new \RuntimeException('This is not supported by the API client implementation');
    }
}
