<?php

namespace ContinuousPipe\Billing\Subscription;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;

interface SubscriptionClient
{
    /**
     * @param UserBillingProfile $billingProfile
     *
     * @return Subscription[]
     */
    public function findSubscriptionsForBillingProfile(UserBillingProfile $billingProfile) : array;
}
