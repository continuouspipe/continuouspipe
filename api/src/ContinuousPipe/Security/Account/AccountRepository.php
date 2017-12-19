<?php

namespace ContinuousPipe\Security\Account;

interface AccountRepository
{
    /**
     * @param string $uuid
     *
     * @throws AccountNotFound
     *
     * @return Account
     */
    public function find(string $uuid);

    /**
     * @param string $username
     *
     * @return Account[]
     */
    public function findByUsername(string $username);

    /**
     * @param string  $username
     * @param Account $account
     */
    public function link(string $username, Account $account);

    /**
     * @param string  $username
     * @param Account $account
     */
    public function unlink(string $username, Account $account);
}
