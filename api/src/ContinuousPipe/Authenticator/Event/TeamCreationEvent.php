<?php

namespace ContinuousPipe\Authenticator\Event;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Symfony\Component\EventDispatcher\Event;

class TeamCreationEvent extends Event
{
    const BEFORE_EVENT = 'before_team_creation';
    const AFTER_EVENT = 'after_team_creation';

    /**
     * @var Team
     */
    private $team;

    /**
     * @var User
     */
    private $creator;

    /**
     * @param Team $team
     * @param User $creator
     */
    public function __construct(Team $team, User $creator)
    {
        $this->team = $team;
        $this->creator = $creator;
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
    public function getCreator()
    {
        return $this->creator;
    }
}
