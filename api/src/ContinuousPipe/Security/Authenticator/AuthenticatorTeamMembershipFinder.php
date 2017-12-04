<?php

namespace ContinuousPipe\Security\Authenticator;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamMembership;
use ContinuousPipe\Security\Team\TeamMembershipFinder;
use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An implementation of the team membership finder which uses an AuthenticatorClient as data source.
 */
class AuthenticatorTeamMembershipFinder implements TeamMembershipFinder
{
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    public function __construct(AuthenticatorClient $authenticatorClient)
    {
        $this->authenticatorClient = $authenticatorClient;
    }

    /**
     * Find team membership by team and user.
     *
     * @param Team $team
     * @param User $user
     *
     * @return TeamMembership|null
     */
    public function findOneByTeamAndUser(Team $team, User $user)
    {
        try {
            $team = $this->authenticatorClient->findTeamBySlug($team->getSlug());
        } catch (TeamNotFound $e) {
            return null;
        }

        $memberships = $team->getMemberships()->filter(function (TeamMembership $membership) use ($user) {
            return $membership->getUser()->getUsername() == $user->getUsername();
        });

        if ($memberships->count() === 1) {
            return $memberships->first();
        }

        return null;
    }
}
