<?php

namespace ContinuousPipe\Billing\Subscription\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Billing\Subscription\SubscriptionException;

class RecurlySubscriptionClient implements SubscriptionClient
{
    public function __construct(string $subdomain, string $apiKey)
    {
        \Recurly_Client::$subdomain = $subdomain;
        \Recurly_Client::$apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionsForBillingProfile(UserBillingProfile $billingProfile): array
    {
        try {
            $recurlySubscriptions = \Recurly_SubscriptionList::getForAccount($billingProfile->getUuid()->toString());
        } catch (\Recurly_NotFoundError $e) {
            return [];
        }

        $subscriptions = [];
        foreach ($recurlySubscriptions as $recurlySubscription) {
            /** @var \Recurly_Subscription $recurlySubscription */
            $subscriptions[] = $this->transformRecurlySubscription($recurlySubscription);
        }

        return $subscriptions;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel(UserBillingProfile $billingProfile, Subscription $subscription): Subscription
    {
        try {
            $recurlySubscription = \Recurly_Subscription::get($subscription->getUuid());
            $recurlySubscription->cancel();
        } catch (\Recurly_Error $e) {
            throw new SubscriptionException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->transformRecurlySubscription($recurlySubscription);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UserBillingProfile $billingProfile, Subscription $subscription): Subscription
    {
        try {
            $recurlySubscription = \Recurly_Subscription::get($subscription->getUuid());
            $recurlySubscription->quantity = $subscription->getQuantity();
            $recurlySubscription->updateImmediately();
        } catch (\Recurly_Error $e) {
            throw new SubscriptionException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->transformRecurlySubscription($recurlySubscription);
    }

    private function transformRecurlySubscription(\Recurly_Subscription $recurlySubscription) : Subscription
    {
        $rawValues = $recurlySubscription->getValues();

        /** @var \Recurly_Plan $plan */
        $plan = $rawValues['plan'];

        return new Subscription(
            $rawValues['uuid'],
            $plan->plan_code,
            $rawValues['state'],
            $rawValues['quantity'],
            $rawValues['unit_amount_in_cents'],
            $rawValues['current_period_started_at'],
            $rawValues['current_period_ends_at'],
            array_key_exists('expires_at', $rawValues) ? $rawValues['expires_at'] : null
        );
    }
}
