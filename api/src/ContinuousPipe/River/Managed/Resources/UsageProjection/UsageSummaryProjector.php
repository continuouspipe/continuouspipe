<?php

namespace ContinuousPipe\River\Managed\Resources\UsageProjection;

use ContinuousPipe\Billing\BillingProfile\BillingProfile;

interface UsageSummaryProjector
{
    public function forFlows(array $flows, \DateTime $left, \DateTime $right, \DateInterval $interval, BillingProfile $billingProfile = null) : UsageSummary;
}
