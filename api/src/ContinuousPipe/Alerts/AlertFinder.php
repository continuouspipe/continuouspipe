<?php

namespace ContinuousPipe\Alerts;

use ContinuousPipe\Security\Team\Team;

interface AlertFinder
{
    /**
     * @param Team $team
     *
     * @return Alert[]
     */
    public function findByTeam(Team $team) : array;
}
