<?php

namespace ContinuousPipe\River\Managed\Resources\Calculation;

use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;

class UsageSnapshotCalculator
{
    /**
     * Resource usage per environment.
     *
     * Array's key is the environment identifier.
     *
     * @var ResourceUsage[]
     */
    private $usagePerEnvironment = [];

    public function updateWith(ResourceUsageHistory $entry)
    {
        if (
            // No history for this environment yet?
            !isset($this->usagePerEnvironment[$entry->getEnvironmentIdentifier()])

            // This usage is greater than the previous one
            || $this->usagePerEnvironment[$entry->getEnvironmentIdentifier()]
        ) {
            $this->usagePerEnvironment[$entry->getEnvironmentIdentifier()] = $entry->getResourcesUsage();
        }
    }

    public function snapshot() : ResourceUsage
    {
        $requests = new AggregateResourcesRequest();
        $limits = new AggregateResourcesRequest();

        foreach ($this->usagePerEnvironment as $usage) {
            $requests->add($usage->getRequests());
            $limits->add($usage->getLimits());
        }

        return new ResourceUsage(
            $requests->toResourcesRequest(),
            $limits->toResourcesRequest()
        );
    }

    /**
     * @return ResourceUsage[]
     */
    public function getUsagePerEnvironment(): array
    {
        return $this->usagePerEnvironment;
    }
}
