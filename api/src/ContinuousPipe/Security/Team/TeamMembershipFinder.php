<?php

namespace ContinuousPipe\Security\Team;

use ContinuousPipe\Security\User\User;

interface TeamMembershipFinder
{
    /**
     * Find team membership by team and user.
     *
     * @param Team $team
     * @param User $user
     *
     * @return TeamMembership|null
     */
    public function findOneByTeamAndUser(Team $team, User $user);
}
