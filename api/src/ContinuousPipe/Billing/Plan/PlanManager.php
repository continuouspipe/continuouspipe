<?php

namespace ContinuousPipe\Billing\Plan;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Security\User\User;

interface PlanManager
{
    public function changePlan(UserBillingProfile $billingProfile, ChangeBillingPlanRequest $changeRequest, User $user) : ChangeBillingPlanResponse;
}
