<?php

namespace ContinuousPipe\Billing\Subscription\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Subscription\Subscription;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use ContinuousPipe\Billing\Subscription\SubscriptionException;

class RecurlySubscriptionClient implements SubscriptionClient
{
    /**
     * @var string
     */
    private $subdomain;

    public function __construct(string $subdomain, string $apiKey)
    {
        $this->subdomain = $subdomain;

        \Recurly_Client::$subdomain = $subdomain;
        \Recurly_Client::$apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionsForBillingProfile(UserBillingProfile $billingProfile): array
    {
        $subscriptions = [];

        try {
            foreach (\Recurly_SubscriptionList::getForAccount($billingProfile->getUuid()->toString()) as $recurlySubscription) {
                /** @var \Recurly_Subscription $recurlySubscription */
                $subscriptions[] = $this->transformRecurlySubscription($billingProfile, $recurlySubscription);
            }
        } catch (\Recurly_NotFoundError $e) {
            return [];
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

        return $this->transformRecurlySubscription($billingProfile, $recurlySubscription);
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

        return $this->transformRecurlySubscription($billingProfile, $recurlySubscription);
    }

    private function transformRecurlySubscription(UserBillingProfile $billingProfile, \Recurly_Subscription $recurlySubscription) : Subscription
    {
        try {
            $hostedAccountToken = \Recurly_Account::get($billingProfile->getUuid())->hosted_login_token;
            $hostedAccountUrl = sprintf(
                'https://%s.recurly.com/account/%s',
                $this->subdomain,
                $hostedAccountToken
            );
        } catch (\Recurly_NotFoundError $e) {
            $hostedAccountUrl = null;
        }

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
            array_key_exists('expires_at', $rawValues) ? $rawValues['expires_at'] : null,
            $hostedAccountUrl
        );
    }
}
