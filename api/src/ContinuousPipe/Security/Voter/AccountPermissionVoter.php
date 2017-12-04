<?php

namespace ContinuousPipe\Security\Voter;

use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountPermissionVoter extends Voter
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
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Account && in_array($attribute, ['ACCESS', 'READ']);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$this->supports($attribute, $subject)) {
            return;
        }

        $securityUser = $token->getUser();
        if (!$securityUser instanceof SecurityUser) {
            return false;
        } elseif (!$subject instanceof Account) {
            throw new \LogicException('Should be an Account object');
        }

        return $this->accountRelatedToTheUser($subject, $securityUser->getUser());
    }

    /**
     * @param Account $account
     * @param User    $user
     *
     * @return bool
     */
    private function accountRelatedToTheUser(Account $account, User $user)
    {
        $userAccounts = $this->accountRepository->findByUsername($user->getUsername());

        foreach ($userAccounts as $userAccount) {
            if ($account->getUuid() == $userAccount->getUuid()) {
                return true;
            }
        }

        return false;
    }
}
