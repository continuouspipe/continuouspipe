<?php

namespace ContinuousPipe\Authenticator\Team\Request;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Security\Team\Team;
use JMS\Serializer\Annotation as JMS;

class TeamPartialUpdateRequest
{
    /**
     * @JMS\Type("ContinuousPipe\Security\Team\Team")
     *
     * @var Team|null
     */
    private $team;

    /**
     * @JMS\Type("ContinuousPipe\Billing\BillingProfile\UserBillingProfile")
     *
     * @var UserBillingProfile|null
     */
    private $billingProfile;

    /**
     * @return Team|null
     */
    public function getTeam()
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
