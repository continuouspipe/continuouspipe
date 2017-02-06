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
     * @throws SubscriptionException
     *
     * @return Subscription
     */
    public function cancel(UserBillingProfile $billingProfile, Subscription $subscription) : Subscription;

    /**
     * @param UserBillingProfile $billingProfile
     * @param Subscription $subscription
     *
     * @throws SubscriptionException
     *
     * @return Subscription
     */
    public function update(UserBillingProfile $billingProfile, Subscription $subscription) : Subscription;
}
