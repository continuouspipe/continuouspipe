<?php

namespace ContinuousPipe\Authenticator\Team\Event;

use ContinuousPipe\Security\Team\Team;
use Symfony\Component\EventDispatcher\Event;

class TeamSaved extends Event
{
    const EVENT_NAME = 'team.saved';

    /**
     * @var Team
     */
    private $team;

    /**
     * @param Team $team
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }
}
