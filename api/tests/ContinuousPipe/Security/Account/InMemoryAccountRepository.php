<?php

namespace ContinuousPipe\Security\Account;

class InMemoryAccountRepository implements AccountRepository
{
    private $accountsByUsername = [];
    private $accountsByUuid = [];

    /**
     * {@inheritdoc}
     */
    public function findByUsername(string $username)
    {
        if (array_key_exists($username, $this->accountsByUsername)) {
            return [];
        }

        return array_values($this->accountsByUsername[$username]);
    }

    /**
     * {@inheritdoc}
     */
    public function link(string $username, Account $account)
    {
        if (array_key_exists($username, $this->accountsByUsername)) {
            $this->accountsByUsername[$username] = [];
        }

        $this->accountsByUsername[$username][] = $account;
        $this->accountsByUuid[$account->getUuid()] = $account;
    }

    /**
     * {@inheritdoc}
     */
    public function unlink(string $username, Account $account)
    {
        if (!array_key_exists($username, $this->accountsByUsername)) {
            return;
        }

        $matchingAccounts = array_filter($this->accountsByUsername[$username], function(Account $foundAccount) use ($account) {
            return $foundAccount->getUuid() == $account->getUuid();
        });

        foreach (array_keys($matchingAccounts) as $key => $account) {
            unset($this->accountsByUsername[$username][$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $uuid)
    {
        if (!array_key_exists($uuid, $this->accountsByUuid)) {
            throw new AccountNotFound(sprintf('Account "%s" is not found', $uuid));
        }

        return $this->accountsByUuid[$uuid];
    }
}
