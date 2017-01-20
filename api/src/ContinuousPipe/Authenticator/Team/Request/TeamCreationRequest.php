<?php

namespace ContinuousPipe\Authenticator\Team\Request;

use ContinuousPipe\Security\Team\Team;
use JMS\Serializer\Annotation as JMS;

class TeamCreationRequest
{
    /**
     * @JMS\Type("ContinuousPipe\Security\Team\Team")
     *
     * @var Team
     */
    private $team;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }
}
