<?php

namespace ContinuousPipe\River\Managed\Resources\UsageProjection;

use ContinuousPipe\Billing\BillingProfile\BillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Model\Component\ResourcesRequest;
use ContinuousPipe\River\Managed\Resources\Calculation\AggregateResourcesRequest;
use ContinuousPipe\River\Managed\Resources\Calculation\ResourceConverter;

class CalculateUsageSummary implements UsageSummaryProjector
{
    /**
     * @var UsageProjector
     */
    private $usageProjector;

    public function __construct(UsageProjector $usageProjector)
    {
        $this->usageProjector = $usageProjector;
    }

    public function forFlows(array $flows, \DateTime $left, \DateTime $right, \DateInterval $interval, UserBillingProfile $billingProfile = null) : UsageSummary
    {
        $usage = $this->usageProjector->forFlows(
            $flows,
            $left,
            $right,
            $interval
        );

        if (empty($usage)) {
            return new UsageSummary();
        }

        $resourcesCalculator = new AggregateResourcesRequest();
        $tides = 0;

        foreach ($usage[0]['entries'] as $entry) {
            $resourcesCalculator->add(new ResourcesRequest(
                $entry['usage']['cpu'],
                $entry['usage']['memory']
            ));

            $tides += $entry['usage']['tides'];
        }

        $aggregatedResources = $resourcesCalculator->toResourcesRequest();

        $usageSummary = new UsageSummary();
        $usageSummary->tides = $tides;
        $usageSummary->memory = $aggregatedResources->getMemory();
        $usageSummary->cpu = $aggregatedResources->getCpu();

        if ($billingProfile != null && null !== ($plan = $billingProfile->getPlan())) {
            if (!empty($availableTides = $plan->getMetrics()->getTides())) {
                $usageSummary->tidesPercent = ResourceConverter::resourceToNumber($usageSummary->tides) / $availableTides * 100;
            }

            if (!empty($availableMemory = $plan->getMetrics()->getMemory())) {
                $usageSummary->memoryPercent = ResourceConverter::resourceToNumber($usageSummary->memory) / ($availableMemory * 1024) * 100;
            }
        }

        return $usageSummary;
    }
}
