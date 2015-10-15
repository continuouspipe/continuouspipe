<?php

namespace ContinuousPipe\Authenticator\Event;

use ContinuousPipe\Security\Team\Team;
use Symfony\Component\EventDispatcher\Event;

class BeforeTeamCreation extends Event
{
    const EVENT_NAME = 'before_team_creation';

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