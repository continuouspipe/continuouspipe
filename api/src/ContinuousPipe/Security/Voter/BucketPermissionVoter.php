<?php

namespace ContinuousPipe\Security\Voter;

use ContinuousPipe\Authenticator\Security\User\SystemUser;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipRepository;
use ContinuousPipe\Security\User\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BucketPermissionVoter extends Voter
{
    /**
     * @var TeamMembershipRepository
     */
    private $teamMembershipRepository;

    /**
     * @param TeamMembershipRepository $teamMembershipRepository
     */
    public function __construct(TeamMembershipRepository $teamMembershipRepository)
    {
        $this->teamMembershipRepository = $teamMembershipRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Bucket && 'ACCESS' == $attribute;
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
        if ($securityUser instanceof SystemUser) {
            return true;
        }

        if (!$securityUser instanceof SecurityUser) {
            return false;
        } elseif (!$subject instanceof Bucket) {
            throw new \LogicException('Should be a Bucket object');
        }

        $user = $securityUser->getUser();
        if ($user->getBucketUuid()->equals($subject->getUuid())) {
            return true;
        }

        $memberships = $this->teamMembershipRepository->findByUser($user);
        $matchingTeamMemberships = $memberships->filter(function (TeamMembership $membership) use ($subject) {
            return $subject->getUuid()->equals($membership->getTeam()->getBucketUuid());
        });

        return $matchingTeamMemberships->count() > 0;
    }
}
