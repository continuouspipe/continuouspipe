<?php

namespace ContinuousPipe\Authenticator\TeamMembership\Event;

use ContinuousPipe\Security\Team\TeamMembership;
use Symfony\Component\EventDispatcher\Event;

class TeamMembershipEvent extends Event
{
    /**
     * @var TeamMembership
     */
    private $teamMembership;

    /**
     * @param TeamMembership $teamMembership
     */
    public function __construct(TeamMembership $teamMembership)
    {
        $this->teamMembership = $teamMembership;
    }

    /**
     * @return TeamMembership
     */
    public function getTeamMembership()
    {
        return $this->teamMembership;
    }
}
