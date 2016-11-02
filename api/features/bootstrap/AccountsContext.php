<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Account\GitHubAccount;
use ContinuousPipe\Security\Account\GoogleAccount;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class AccountsContext implements Context
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @param AccountRepository $accountRepository
     * @param KernelInterface $kernel
     */
    public function __construct(AccountRepository $accountRepository, KernelInterface $kernel)
    {
        $this->accountRepository = $accountRepository;
        $this->kernel = $kernel;
    }

    /**
     * @Given I have a connected GitHub account :uuid for the user :username
     * @Given there is a connected GitHub account :uuid for the user :username
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
     * @Given there is connected Google account :uuid for the user :username
     */
    public function thereIsConnectedGoogleAccountForTheUser($uuid, $username)
    {
        $this->accountRepository->link($username, new GoogleAccount(
            $uuid,
            $username,
            $username.'@example.com',
            'REFRESH_TOKEN'
        ));
    }

    /**
     * @When I request the list of my accounts
     */
    public function iRequestTheListOfMyAccounts()
    {
        $this->response = $this->kernel->handle(Request::create('/api/me/accounts'));
    }

    /**
     * @Then I should see the :type account :uuid
     */
    public function iShouldSeeTheGithubAccount($type, $uuid)
    {
        if (null === $this->findAccountInResponse($this->response, $type, $uuid)) {
            throw new \RuntimeException('Account not found');
        }
    }

    /**
     * @Then I should not see the :type account :uuid
     */
    public function iShouldNotSeeTheGoogleAccount($type, $uuid)
    {
        if (null !== $this->findAccountInResponse($this->response, $type, $uuid)) {
            throw new \RuntimeException('Account found');
        }
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

    private function findAccountInResponse(Response $response, $type, $uuid)
    {
        if ($response->getStatusCode() != 200) {
            echo $response->getContent();

            throw new \RuntimeException(sprintf('Expected status code 200, got %d', $response->getStatusCode()));
        }

        $json = json_decode($response->getContent(), true);
        if (!is_array($json)) {
            throw new \RuntimeException('Unexpected non-JSON resposne');
        }

        $type = strtolower($type);
        $matchingAccount = array_filter($json, function(array $account) use ($type, $uuid) {
            return $account['type'] == $type && $account['uuid'] == $uuid;
        });

        return current($matchingAccount) ?: null;
    }
}
