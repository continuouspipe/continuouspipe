<?php

namespace ContinuousPipe\Security\Team;

use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;

interface TeamMembershipRepository
{
    /**
     * Find team memberships by user.
     *
     * @param User $user
     *
     * @return TeamMembership[]|TeamMembershipCollection
     */
    public function findByUser(User $user);

    /**
     * Find team memberships by team.
     *
     * @param Team $team
     *
     * @return TeamMembership[]|TeamMembershipCollection
     */
    public function findByTeam(Team $team);

    /**
     * Save the given team membership.
     *
     * @param TeamMembership $membership
     */
    public function save(TeamMembership $membership);

    /**
     * Remove the given membership.
     *
     * @param TeamMembership $membership
     */
    public function remove(TeamMembership $membership);
}
