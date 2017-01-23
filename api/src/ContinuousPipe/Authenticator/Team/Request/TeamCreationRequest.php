<?php

namespace ContinuousPipe\Authenticator\Team\Request;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
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
     * @JMS\Type("ContinuousPipe\Billing\BillingProfile\UserBillingProfile")
     *
     * @var UserBillingProfile|null
     */
    private $billingProfile;

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return UserBillingProfile|null
     */
    public function getBillingProfile()
    {
        return $this->billingProfile;
    }
}
