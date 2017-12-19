<?php

namespace ContinuousPipe\Billing\Plan\Repository;

use ContinuousPipe\Billing\Plan\AddOn;
use ContinuousPipe\Billing\Plan\Plan;

interface PlanRepository
{
    /**
     * @return Plan[]
     */
    public function findPlans() : array;

    /**
     * @return AddOn[]
     */
    public function findAddOns() : array;

    public function findPlanByIdentifier(string $identifier) : Plan;
}
