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
    private $cancelledSubscriptions = [];

    /**
     * @var Subscription[]
     */
    private $updatedSubscriptions = [];

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

        $this->cancelledSubscriptions[] = $subscription;

        return $subscription;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UserBillingProfile $billingProfile, Subscription $subscription): Subscription
    {
        $subscription = $this->decoratedClient->update($billingProfile, $subscription);

        $this->updatedSubscriptions[] = $subscription;

        return $subscription;
    }

    /**
     * @return Subscription[]
     */
    public function getCancelledSubscriptions(): array
    {
        return $this->cancelledSubscriptions;
    }

    /**
     * @return Subscription[]
     */
    public function getUpdatedSubscriptions(): array
    {
        return $this->updatedSubscriptions;
    }
}
