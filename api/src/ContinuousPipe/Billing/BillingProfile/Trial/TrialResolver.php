<?php

namespace ContinuousPipe\Billing\BillingProfile\Trial;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;

interface TrialResolver
{
    /**
     * Return the expiration date of the trial period for this billing profile.
     *
     * @param UserBillingProfile $billingProfile
     *
     * @return \DateTimeInterface
     */
    public function getTrialPeriodExpirationDate(UserBillingProfile $billingProfile) : \DateTimeInterface;
}
