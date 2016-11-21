<?php

namespace ContinuousPipe\Security\Voter;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\User\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Team && in_array($attribute, ['ADMIN', 'READ']);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof SecurityUser) {
            return false;
        } elseif (!$subject instanceof Team) {
            throw new \LogicException('Should be a Team object');
        }

        $user = $user->getUser();
        $matchingMemberships = $subject->getMemberships()->filter(function (TeamMembership $membership) use ($user) {
            return $membership->getUser()->getUsername() == $user->getUsername();
        });

        if ($matchingMemberships->count() == 0) {
            return false;
        }

        /** @var TeamMembership $membership */
        $membership = $matchingMemberships->first();

        if ('READ' == $attribute) {
            return true;
        }

        return in_array('ADMIN', $membership->getPermissions());
    }
}
