<?php

namespace ContinuousPipe\Authenticator\Security\Voter;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\User\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserBillingProfileVoter extends Voter
{
    const READ = 'READ';

    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    public function __construct(
        UserBillingProfileRepository $userBillingProfileRepository
    ) {
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (self::READ !== $attribute) {
            return false;
        }

        if (!$subject instanceof UserBillingProfile) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var UserBillingProfile $billingProfile */
        $billingProfile = $subject;

        /** @var SecurityUser $user */
        $user = $token->getUser();
        if ($user instanceof SecurityUser && $billingProfile->isAdmin($user->getUser())) {
            return true;
        }

        return $billingProfile->getTeams()->filter(function (Team $team) use ($user) {
            $adminUserMemberships = $team->getMemberships()->filter(function (TeamMembership $membership) use ($user) {
                return $membership->getUser()->getUsername() == $user->getUsername();
            })->filter(function (TeamMembership $membership) {
                return in_array(TeamMembership::PERMISSION_ADMIN, $membership->getPermissions());
            });

            return $adminUserMemberships->count() > 0;
        })->count() > 0;
    }
}
