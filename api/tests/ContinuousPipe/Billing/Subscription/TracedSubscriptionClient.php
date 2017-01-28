<?php

namespace ContinuousPipe\Billing\Subscription;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;

class TracedSubscriptionClient implements SubscriptionClient
{
    /**
     * @var SubscriptionClient
     */
    private $decoratedClient;

    /**
     * @var Subscription[]
     */
    private $canceledSubscriptions = [];

    /**
     * @param SubscriptionClient $decoratedClient
     */
    public function __construct(SubscriptionClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionsForBillingProfile(UserBillingProfile $billingProfile): array
    {
        return $this->decoratedClient->findSubscriptionsForBillingProfile($billingProfile);
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(UserBillingProfile $billingProfile, Subscription $subscription): Subscription
    {
        $subscription = $this->decoratedClient->cancel($billingProfile, $subscription);

        $this->canceledSubscriptions[] = $subscription;

        return $subscription;
    }

    /**
     * @return Subscription[]
     */
    public function getCanceledSubscriptions(): array
    {
        return $this->canceledSubscriptions;
    }
}
