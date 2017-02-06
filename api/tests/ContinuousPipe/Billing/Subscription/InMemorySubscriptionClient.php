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
    public function cancel(UserBillingProfile $billingProfile, Subscription $subscription): Subscription
    {
        $subscription = $subscription->withState(Subscription::STATE_CANCELED);

        $this->addSubscription($billingProfile->getUuid(), $subscription);

        return $subscription;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UserBillingProfile $billingProfile, Subscription $subscription): Subscription
    {
        $this->addSubscription($billingProfile->getUuid(), $subscription);

        return $subscription;
    }

    public function addSubscription(UuidInterface $billingProfileUuid, Subscription $subscription)
    {
        if (!array_key_exists($billingProfileUuid->toString(), $this->subscriptionsByProfileUuid)) {
            $this->subscriptionsByProfileUuid[$billingProfileUuid->toString()] = [];
        }

        foreach ($this->subscriptionsByProfileUuid[$billingProfileUuid->toString()] as $index => $existingSubscription) {
            if ($existingSubscription->getUuid() == $subscription->getUuid()) {
                $this->subscriptionsByProfileUuid[$billingProfileUuid->toString()][$index] = $subscription;

                return;
            }
        }

        $this->subscriptionsByProfileUuid[$billingProfileUuid->toString()][] = $subscription;
    }
}
