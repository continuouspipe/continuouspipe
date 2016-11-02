<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Account\GitHubAccount;
use Ramsey\Uuid\Uuid;

class AccountsContext implements Context
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @Given I have a connected GitHub account :uuid for the user :username
     */
    public function iHaveAConnectedGithubAccountForTheUser($uuid, $username)
    {
        $this->accountRepository->link($username, new GitHubAccount(
            $uuid,
            '1234567',
            $username,
            'token'
        ));
    }

    /**
     * @When I unlink the account :uuid from the user :username
     */
    public function iUnlinkTheAccountFromTheUser($uuid, $username)
    {
        $this->accountRepository->unlink(
            $username,
            $this->accountRepository->find($uuid)
        );
    }

    /**
     * @Then the account :uuid should not be linked to the user :username
     */
    public function theAccountShouldNotBeLinkedToTheUser($uuid, $username)
    {
        $matchingAccounts = array_filter($this->accountRepository->findByUsername($username), function(Account $account) use ($uuid) {
            return $account->getUuid() == $uuid;
        });

        if (count($matchingAccounts) != 0) {
            throw new \RuntimeException('Found matching account');
        }
    }
}