<?php


namespace ContinuousPipe\Authenticator\Security\Voter;

use ContinuousPipe\Security\Credentials\Cluster;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleInterface;

class ClusterPermissionVoter extends Voter
{
    const EDIT = 'EDIT';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (self::EDIT !== $attribute) {
            return false;
        }

        if (!$subject instanceof Cluster) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var Cluster $cluster */
        $cluster = $subject;

        if ($this->isManagedCluster($cluster) && !$this->isAdmin($token)) {
            return false;
        }

        return true;
    }

    /**
     * @param Cluster $cluster
     *
     * @return bool
     */
    private function isManagedCluster(Cluster $cluster)
    {
        foreach ($cluster->getPolicies() as $policy) {
            if ($policy->getName() == 'managed') {
                return true;
            }
        }

        return false;
    }

    private function isAdmin(TokenInterface $token)
    {
        return in_array('ROLE_ADMIN', array_map(function (RoleInterface $role) {
            return $role->getRole();
        }, $token->getRoles()));
    }
}
