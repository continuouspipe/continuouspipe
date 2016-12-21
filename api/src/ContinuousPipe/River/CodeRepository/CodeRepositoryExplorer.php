<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Account\Account;

interface CodeRepositoryExplorer
{
    /**
     * Find the repositories of the given user.
     *
     * @param Account $account
     *
     * @throws CodeRepositoryException
     *
     * @return CodeRepository[]
     */
    public function findUserRepositories(Account $account) : array;

    /**
     * Find the organisations of the given account.
     *
     * @param Account $account
     *
     * @throws CodeRepositoryException
     *
     * @return Organisation[]
     */
    public function findOrganisations(Account $account) : array;

    /**
     * Find the repositories of the given organisation with the given account.
     *
     * @param Account $account
     * @param string $organisationIdentifier
     *
     * @throws CodeRepositoryException
     *
     * @return CodeRepository[]
     */
    public function findOrganisationRepositories(Account $account, string $organisationIdentifier) : array;

    /**
     * Returns true if the explorer supports the given account.
     *
     * @param Account $account
     *
     * @return bool
     */
    public function supports(Account $account) : bool ;
}
