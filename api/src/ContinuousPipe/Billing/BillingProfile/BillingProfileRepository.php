<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Billing\BillingException;
use ContinuousPipe\Security\Team\Team;

interface BillingProfileRepository
{
    /**
     * @param Team $team
     *
     * @throws BillingProfileNotFound
     * @throws BillingException
     *
     * @return BillingProfile
     */
    public function findByTeam(Team $team) : BillingProfile;
}
