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
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var RecurlyClient
     */
    private $recurlyClient;

    public function __construct(PlanRepository $planRepository, LoggerInterface $logger, EntityManager $entityManager, UrlGeneratorInterface $urlGenerator, RecurlyClient $recurlyClient)
    {
        $this->planRepository = $planRepository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->recurlyClient = $recurlyClient;
    }

    public function changePlan(UserBillingProfile $billingProfile, ChangeBillingPlanRequest $changeRequest, User $user) : ChangeBillingPlanResponse
    {
        if (null === ($subscription = $this->subscriptionByBillingProfile($billingProfile))) {
            return new ChangeBillingPlanResponse($billingProfile, $this->urlGenerator->generate('billing_redirection_out', [
                'to' => sprintf(
                    '%s?%s',
                    $this->recurlyClient->subscribeUrl($changeRequest->getPlan(), $billingProfile->getUuid()->toString(), $user->getUsername()),
                    http_build_query([
                        'quantity' => 1,
                        'email' => $user->getEmail()
                    ])
                )
            ], UrlGeneratorInterface::ABSOLUTE_URL));
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

    public function getInvoicesUrl(UserBillingProfile $billingProfile)
    {
        try {
            $account = \Recurly_Account::get($billingProfile->getUuid());
        } catch (\Recurly_NotFoundError $e) {
            return null;
        }

        return $this->recurlyClient->accountUrl($account);
    }

    public function refreshBillingProfile(UserBillingProfile $billingProfile) : UserBillingProfile
    {
        if (null === ($subscription = $this->subscriptionByBillingProfile($billingProfile))) {
            return $billingProfile;
        }

        $updatedBillingProfile = $this->getUpdatedBillingProfile($billingProfile, $subscription);

        $this->entityManager->persist($updatedBillingProfile);
        $this->entityManager->flush();

        return $updatedBillingProfile;
    }

    private function getUpdatedBillingProfile(UserBillingProfile $billingProfile, \Recurly_Subscription $subscription)
    {
        $billingProfile = $billingProfile
            ->setPlan(
                $this->planRepository->findPlanByIdentifier($subscription->plan->plan_code)
            )
            ->setStatus($subscription->state)
        ;

        if (isset($subscription->trial_ends_at)) {
            if (is_string($subscription->trial_ends_at)) {
                $subscription->trial_ends_at = new \DateTime($subscription->trial_ends_at);
            }

            if (!$subscription->trial_ends_at instanceof \DateTime) {
                throw new \InvalidArgumentException(sprintf(
                    '`trial_ends_at` type (%s) is not matching expected type',
                    gettype($subscription->trial_ends_at)
                ));
            }

            $billingProfile = $billingProfile->setTrialEndDate($subscription->trial_ends_at);
        }

        return $billingProfile;
    }

    private function changeRecurlySubscription(\Recurly_Subscription $subscription, ChangeBillingPlanRequest $changeRequest)
    {
        try {
            $recurlySubscription = $this->recurlyClient->getSubscription($subscription->uuid);
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
            $list = $this->recurlyClient->subscriptionsForAccount($billingProfile->getUuid()->toString());

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
