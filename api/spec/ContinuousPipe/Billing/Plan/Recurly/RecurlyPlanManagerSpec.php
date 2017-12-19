<?php

namespace spec\ContinuousPipe\Billing\Plan\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Plan\Metrics;
use ContinuousPipe\Billing\Plan\Plan;
use ContinuousPipe\Billing\Plan\Recurly\RecurlyClient;
use ContinuousPipe\Billing\Plan\Repository\PlanRepository;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecurlyPlanManagerSpec extends ObjectBehavior
{
    function let(PlanRepository $planRepository, EntityManager $entityManager, UrlGeneratorInterface $urlGenerator, RecurlyClient $recurlyClient)
    {
        $planRepository->findPlanByIdentifier('starter')->willReturn(new Plan(
            'starter',
            'Starter',
            150,
            new Metrics(100, 1, 1, 1)
        ));

        $this->beConstructedWith($planRepository, new NullLogger(), $entityManager, $urlGenerator, $recurlyClient);
    }

    function it_transforms_the_billing_profile_with_plan_status(RecurlyClient $recurlyClient)
    {
        $billingProfile = new UserBillingProfile(Uuid::uuid4(), 'Name', new \DateTime());

        $subscriptions = $this->recurlySubscriptions([
            $this->recurlySubscription([
                'state' => 'active',
                'plan' => [
                    'plan_code' => 'starter',
                ]
            ])
        ]);

        $recurlyClient->subscriptionsForAccount($billingProfile->getUuid()->toString())->willReturn($subscriptions);

        $this->refreshBillingProfile($billingProfile)->shouldHavePlan('starter');
        $this->refreshBillingProfile($billingProfile)->shouldHaveStatus('active');
    }

    function it_transforms_the_billing_profile_with_trial_end_date_if_any(RecurlyClient $recurlyClient)
    {
        $billingProfile = new UserBillingProfile(Uuid::uuid4(), 'Name', new \DateTime());

        $subscriptions = $this->recurlySubscriptions([
            $this->recurlySubscription([
                'state' => 'active',
                'plan' => [
                    'plan_code' => 'starter',
                ],
                'trial_ends_at' => '2016-08-11T22:36:22Z'
            ])
        ]);

        $recurlyClient->subscriptionsForAccount($billingProfile->getUuid()->toString())->willReturn($subscriptions);

        $this->refreshBillingProfile($billingProfile)->shouldHaveTrialEndDate(new \DateTime('2016-08-11T22:36:22Z'));
    }

    public function getMatchers()
    {
        return [
            'havePlan' => function(UserBillingProfile $billingProfile, string $plan) {
                return $billingProfile->getPlan()->getIdentifier() == $plan;
            },
            'haveStatus' => function(UserBillingProfile $billingProfile, string $status) {
                return $billingProfile->getStatus() == $status;
            },
            'haveTrialEndDate' => function(UserBillingProfile $billingProfile, \DateTime $date) {
                return $billingProfile->getTrialEndDate() == $date;
            },
        ];
    }

    function recurlySubscription(array $descriptor)
    {
        $subscription = new \Recurly_Subscription();

        foreach ($descriptor as $key => $value) {
            if (is_array($value)) {
                $value = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($value));
            }

            $subscription->$key = $value;
        }

        return $subscription;
    }

    function recurlySubscriptions(array $subscriptions)
    {
        $list = new \Recurly_SubscriptionList();

        $property = new \ReflectionProperty($list, '_objects');
        $property->setAccessible(true);
        $property->setValue($list, $subscriptions);

        return $list;
    }
}
