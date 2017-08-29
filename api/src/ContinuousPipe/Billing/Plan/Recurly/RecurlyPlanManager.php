<?php

namespace ContinuousPipe\Billing\Plan\Recurly;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Plan\ChangeBillingPlanRequest;
use ContinuousPipe\Billing\Plan\ChangeBillingPlanResponse;
use ContinuousPipe\Billing\Plan\PlanManager;
use ContinuousPipe\Billing\Plan\Repository\PlanRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class RecurlyPlanManager implements PlanManager
{
    /**
     * @var PlanRepository
     */
    private $planRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $subdomain;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(PlanRepository $planRepository, LoggerInterface $logger, EntityManager $entityManager, string $subdomain, string $apiKey)
    {
        $this->planRepository = $planRepository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->subdomain = $subdomain;

        \Recurly_Client::$subdomain = $subdomain;
        \Recurly_Client::$apiKey = $apiKey;
    }

    public function changePlan(UserBillingProfile $billingProfile, ChangeBillingPlanRequest $changeRequest, User $user) : ChangeBillingPlanResponse
    {
        if (null === ($subscription = $this->subscriptionByBillingProfile($billingProfile))) {
            return new ChangeBillingPlanResponse($billingProfile, sprintf(
                'https://%s.recurly.com/subscribe/%s/%s/%s?%s',
                $this->subdomain,
                $changeRequest->getPlan(),
                $billingProfile->getUuid()->toString(),
                urlencode($user->getUsername()),
                http_build_query([
                    'quantity' => 1,
                    'email' => $user->getEmail()
                ])
            ));
        }

        $updatedBillingProfile = $this->getUpdatedBillingProfile(
            $billingProfile,
            $this->changeRecurlySubscription($subscription, $changeRequest)
        );

        $this->entityManager->persist($updatedBillingProfile);
        $this->entityManager->flush();

        return new ChangeBillingPlanResponse(
            $updatedBillingProfile
        );
    }

    private function getUpdatedBillingProfile(UserBillingProfile $billingProfile, \Recurly_Subscription $subscription)
    {
        return $billingProfile->withPlan(
            $this->planRepository->findPlanByIdentifier($subscription->plan_code)
        );
    }

    private function changeRecurlySubscription(\Recurly_Subscription $subscription, ChangeBillingPlanRequest $changeRequest)
    {
        try {
            $recurlySubscription = \Recurly_Subscription::get($subscription->uuid);
            $recurlySubscription->plan_code = $changeRequest->getPlan();
            $recurlySubscription->updateImmediately();
        } catch (\Recurly_Error $e) {
            throw $e;
        }

        return $recurlySubscription;
    }

    /**
     * @param UserBillingProfile $billingProfile
     *
     * @return null|\Recurly_Subscription
     */
    private function subscriptionByBillingProfile(UserBillingProfile $billingProfile)
    {
        try {
            $list = \Recurly_SubscriptionList::getForAccount($billingProfile->getUuid()->toString());

            if ($list->count() == 0) {
                return null;
            } elseif ($list->count() > 1) {
                $this->logger->warning('Found more than one Recurly subscription for this client', [
                    'billing_profile_uuid' => $billingProfile->getUuid()->toString(),
                    'billing_profile_name' => $billingProfile->getName(),
                ]);
            }

            return $list->current();
        } catch (\Recurly_NotFoundError $e) {
            return null;
        }
    }
}
