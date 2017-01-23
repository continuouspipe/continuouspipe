<?php

namespace ContinuousPipe\Billing\Infrastructure\Doctrine;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Security\Team\Team;

class UserBillingProfileTeamRelation
{
    /**
     * @var Team
     */
    private $team;

    /**
     * @var UserBillingProfile
     */
    private $userBillingProfile;

    public function __construct(Team $team, UserBillingProfile $userBillingProfile)
    {
        $this->team = $team;
        $this->userBillingProfile = $userBillingProfile;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return UserBillingProfile
     */
    public function getUserBillingProfile(): UserBillingProfile
    {
        return $this->userBillingProfile;
    }
}
