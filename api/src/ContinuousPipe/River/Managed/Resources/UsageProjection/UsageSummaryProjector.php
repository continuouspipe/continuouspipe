<?php

namespace ContinuousPipe\River\Managed\Resources\UsageProjection;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;

interface UsageSummaryProjector
{
    public function forFlows(array $flows, \DateTime $left, \DateTime $right, \DateInterval $interval, UserBillingProfile $billingProfile = null) : UsageSummary;
}
