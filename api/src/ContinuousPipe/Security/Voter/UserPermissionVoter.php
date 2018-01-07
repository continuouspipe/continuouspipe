<?php

namespace ContinuousPipe\Security\Voter;

use ContinuousPipe\Security\User\SecurityUser;
use ContinuousPipe\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserPermissionVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof User && in_array($attribute, ['ADMIN', 'VIEW']);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $securityUser = $token->getUser();
        if (!$securityUser instanceof SecurityUser) {
            return false;
        } elseif (!$subject instanceof User) {
            throw new \LogicException('Should be a User object');
        }

        return $securityUser->getUser()->getUsername() == $subject->getUsername();
    }
}
