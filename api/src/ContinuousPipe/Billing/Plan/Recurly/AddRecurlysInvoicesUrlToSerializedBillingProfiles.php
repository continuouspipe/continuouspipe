<?php

namespace ContinuousPipe\Billing\Plan\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

class AddRecurlysInvoicesUrlToSerializedBillingProfiles implements EventSubscriberInterface
{
    /**
     * @var SubscriptionClient
     */
    private $subscriptionClient;

    /**
     * @param SubscriptionClient $subscriptionClient
     */
    public function __construct(SubscriptionClient $subscriptionClient)
    {
        $this->subscriptionClient = $subscriptionClient;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => UserBillingProfile::class,
                'direction' => 'serialization',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var UserBillingProfile $object */
        $object = $event->getObject();

        if (null !== ($subscription = $this->getSubscription($object))) {
            $event->getVisitor()->addData('invoices_url', $subscription->getHostedDetailsUrl());
        }
    }

    private function getSubscription(UserBillingProfile $billingProfile)
    {
        $subscriptions = $this->subscriptionClient->findSubscriptionsForBillingProfile($billingProfile);

        if (count($subscriptions) == 0) {
            return null;
        }

        return $subscriptions[0];
    }
}
