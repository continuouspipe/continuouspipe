<?php

namespace ContinuousPipe\Billing\Plan\Recurly;

class RecurlyClient
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

    public function subscribeUrl(string $plan, string $account, string $username) : string
    {
        return sprintf(
            'https://%s.recurly.com/subscribe/%s/%s/%s',
            $this->subdomain,
            $plan,
            $account,
            urlencode($username)
        );
    }

    public function accountUrl(\Recurly_Account $account) : string
    {
        return sprintf(
            'https://%s.recurly.com/account/%s',
            $this->subdomain,
            $account->hosted_login_token
        );
    }

    public function getSubscription($uuid) : \Recurly_Subscription
    {
        return \Recurly_Subscription::get($uuid);
    }

    public function subscriptionsForAccount($account) : \Recurly_SubscriptionList
    {
        return \Recurly_SubscriptionList::getForAccount($account);
    }
}
