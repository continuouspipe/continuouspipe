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

    /**
     * @param UserBillingProfile $billingProfile
     * @param Subscription $subscription
     *
     * @return Subscription
     */
    public function cancel(UserBillingProfile $billingProfile, Subscription $subscription) : Subscription;
}
