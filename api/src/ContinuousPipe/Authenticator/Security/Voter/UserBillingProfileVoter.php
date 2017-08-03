<?php

namespace ContinuousPipe\Authenticator\Security\Voter;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserBillingProfileVoter extends Voter
{
    const VIEW = 'view';

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
        if (self::VIEW !== $attribute) {
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
        $user = $token->getUser();

        if ($subject->getUser()->getUsername() == $user->getUsername()) {
            return true;
        }

        $teams = $this->userBillingProfileRepository->findRelations($subject->getUuid());
        $teamsUserIsAdmin = array_filter($teams, function (Team $team) use ($user) {
            $adminUserMemberships = $team->getMemberships()->filter(function (TeamMembership $membership) use ($user) {
                return $membership->getUser()->getUsername() == $user->getUsername();
            })->filter(function (TeamMembership $membership) {
                return in_array(TeamMembership::PERMISSION_ADMIN, $membership->getPermissions());
            });

            return $adminUserMemberships->count() > 0;
        });

        return count($teamsUserIsAdmin) > 0;
    }
}
