<?php

namespace ContinuousPipe\Billing\Plan;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\Plan\Repository\PlanRepository;
use ContinuousPipe\Security\User\User;

class NullPlanManager implements PlanManager
{
    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * @param PlanRepository $planRepository
     */
    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function changePlan(UserBillingProfile $billingProfile, ChangeBillingPlanRequest $changeRequest, User $user): ChangeBillingPlanResponse
    {
        return new ChangeBillingPlanResponse($billingProfile->setPlan(
            $this->planRepository->findPlanByIdentifier($changeRequest->getPlan())
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getInvoicesUrl(UserBillingProfile $billingProfile)
    {
        if (null !== $billingProfile->getPlan()) {
            return 'https://google.com';
        }

        return null;
    }
}
