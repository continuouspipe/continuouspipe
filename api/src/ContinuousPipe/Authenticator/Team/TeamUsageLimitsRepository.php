<?php

namespace ContinuousPipe\Authenticator\Team;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamUsageLimits;

interface TeamUsageLimitsRepository
{
    public function findByTeam(Team $team) : TeamUsageLimits;

    public function save(Team $team, TeamUsageLimits $limits);
}
