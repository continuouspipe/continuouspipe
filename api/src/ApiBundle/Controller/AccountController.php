<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.account")
 */
class AccountController
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
     * @Route("/me/accounts", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @View
     */
    public function userAccountsAction(User $user)
    {
        return $this->accountsByUserAction($user);
    }

    /**
     * @Route("/users/{username}/accounts", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"byUsername"="username"})
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
     * @ParamConverter("account", converter="account")
     * @Security("is_granted('ACCESS', account)")
     * @View
     */
    public function getAction(Account $account)
    {
        return $account;
    }

    /**
     * @Route("/accounts/{accountUuid}/unlink", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("account", converter="account", options={"uuid"="accountUuid"})
     * @View
     */
    public function unlinkAction(User $user, Account $account)
    {
        $this->accountRepository->unlink($user->getUsername(), $account);
    }

}
