<?php

namespace ContinuousPipe\Billing\Subscription\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;

class RecurlySubscriptionClient implements SubscriptionClient
{
    /**
     * @param UserBillingProfile $billingProfile
     *
     * @return Subscription[]
     */
    public function findSubscriptionsForBillingProfile(UserBillingProfile $billingProfile): array
    {
        return [];
    }
}
