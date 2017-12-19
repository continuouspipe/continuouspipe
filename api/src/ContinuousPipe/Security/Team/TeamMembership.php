<?php

namespace ContinuousPipe\Security\Team;

use ContinuousPipe\Security\User\User;

class TeamMembership
{
    const PERMISSION_ADMIN = 'ADMIN';

    /**
     * @var Team
     */
    private $team;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param Team  $team
     * @param User  $user
     * @param array $permissions
     */
    public function __construct(Team $team, User $user, array $permissions = [])
    {
        $this->team = $team;
        $this->user = $user;
        $this->permissions = $permissions;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions ?: [];
    }

    public function isAdmin() : bool
    {
        return in_array(self::PERMISSION_ADMIN, $this->getPermissions());
    }
}
