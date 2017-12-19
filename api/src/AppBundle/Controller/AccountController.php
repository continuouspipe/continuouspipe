<?php

namespace AppBundle\Controller;

use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\User\User;
use ContinuousPipe\River\CodeRepository\CodeRepositoryExplorer;
use ContinuousPipe\Security\Account\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.account")
 */
class AccountController
{
    /**
     * @var CodeRepositoryExplorer
     */
    private $codeRepositoryExplorer;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @param CodeRepositoryExplorer $codeRepositoryExplorer
     */
    public function __construct(
        CodeRepositoryExplorer $codeRepositoryExplorer,
        AccountRepository $accountRepository
    ) {
        $this->codeRepositoryExplorer = $codeRepositoryExplorer;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @Route("/account/{accountUuid}/organisations", methods={"GET"})
     * @ParamConverter("account", converter="account", options={"uuid"="accountUuid"})
     * @Security("is_granted('READ', account)")
     * @View
     */
    public function listOrganisations(Account $account)
    {
        return $this->codeRepositoryExplorer->findOrganisations($account);
    }

    /**
     * @Route("/account/{accountUuid}/repositories", methods={"GET"})
     * @ParamConverter("account", converter="account", options={"uuid"="accountUuid"})
     * @Security("is_granted('READ', account)")
     * @View
     */
    public function listUserRepositories(Account $account)
    {
        return $this->codeRepositoryExplorer->findUserRepositories($account);
    }

    /**
     * @Route("/account/{accountUuid}/organisations/{organisationIdentifier}/repositories", methods={"GET"})
     * @ParamConverter("account", converter="account", options={"uuid"="accountUuid"})
     * @Security("is_granted('READ', account)")
     * @View
     */
    public function listOrganisationRepositories(Account $account, string $organisationIdentifier)
    {
        return $this->codeRepositoryExplorer->findOrganisationRepositories($account, $organisationIdentifier);
    }

    /**
     * @Route("/me/accounts", methods={"GET"})
     * @ParamConverter("user", converter="authenticator_user", options={"fromSecurityContext"=true})
     * @View
     */
    public function userAccountsAction(User $user)
    {
        return $this->accountsByUserAction($user);
    }

    /**
     * @Route("/users/{username}/accounts", methods={"GET"})
     * @ParamConverter("user", converter="authenticator_user", options={"byUsername"="username"})
     * @Security("is_granted('VIEW', user)")
     * @View
     */
    public function accountsByUserAction(User $user)
    {
        return $this->accountRepository->findByUsername(
            $user->getUsername()
        );
    }

    /**
     * @Route("/accounts/{uuid}", methods={"GET"})
     * @ParamConverter("account", converter="authenticator_account")
     * @Security("is_granted('ACCESS', account)")
     * @View
     */
    public function getAction(Account $account)
    {
        return $account;
    }

    /**
     * @Route("/accounts/{accountUuid}/unlink", methods={"POST"})
     * @ParamConverter("user", converter="authenticator_user", options={"fromSecurityContext"=true})
     * @ParamConverter("account", converter="authenticator_account", options={"uuid"="accountUuid"})
     * @View
     */
    public function unlinkAction(User $user, Account $account)
    {
        $this->accountRepository->unlink($user->getUsername(), $account);
    }
}
