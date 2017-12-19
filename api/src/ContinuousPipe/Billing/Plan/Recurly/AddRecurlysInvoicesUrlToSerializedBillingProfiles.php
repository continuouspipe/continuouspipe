<?php

namespace ContinuousPipe\Billing\Plan\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Plan\PlanManager;
use ContinuousPipe\Billing\Plan\Repository\PlanRepository;
use ContinuousPipe\Billing\Subscription\SubscriptionClient;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

class AddRecurlysInvoicesUrlToSerializedBillingProfiles implements EventSubscriberInterface
{
    /**
     * @var PlanManager
     */
    private $planManager;

    public function __construct(PlanManager $planManager)
    {
        $this->planManager = $planManager;
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

        if (null !== ($url = $this->planManager->getInvoicesUrl($object))) {
            $event->getVisitor()->addData('invoices_url', $url);
        }
    }
}
