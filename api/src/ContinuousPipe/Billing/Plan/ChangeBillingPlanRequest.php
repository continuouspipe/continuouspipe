<?php

namespace ContinuousPipe\Billing\Plan;

use JMS\Serializer\Annotation as JMS;

class ChangeBillingPlanRequest
{
    /**
     * Identifier of the plan.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $plan;

    /**
     * @return string
     */
    public function getPlan(): string
    {
        return $this->plan;
    }
}
