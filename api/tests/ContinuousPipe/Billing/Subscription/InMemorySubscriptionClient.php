<?php

namespace ContinuousPipe\Billing\Subscription;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use Ramsey\Uuid\UuidInterface;

class InMemorySubscriptionClient implements SubscriptionClient
{
    /**
     * @var array<string,Subscription[]>
     */
    private $subscriptionsByProfileUuid = [];

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionsForBillingProfile(UserBillingProfile $billingProfile): array
    {
        if (!array_key_exists($billingProfile->getUuid()->toString(), $this->subscriptionsByProfileUuid)) {
            return [];
        }

        return $this->subscriptionsByProfileUuid[$billingProfile->getUuid()->toString()];
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscription(UuidInterface $billingProfileUuid, Subscription $subscription)
    {
        if (!array_key_exists($billingProfileUuid->toString(), $this->subscriptionsByProfileUuid)) {
            $this->subscriptionsByProfileUuid[$billingProfileUuid->toString()] = [];
        }

        $this->subscriptionsByProfileUuid[$billingProfileUuid->toString()][] = $subscription;
    }
}
