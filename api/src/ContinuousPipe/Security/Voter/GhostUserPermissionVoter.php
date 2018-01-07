<?php

namespace ContinuousPipe\Security\Voter;

use ContinuousPipe\Security\User\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class GhostUserPermissionVoter implements VoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $user = $token->getUser();
        if (!$user instanceof SecurityUser) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        return in_array('ROLE_GHOST', $user->getRoles()) ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_ABSTAIN;
    }
}
