<?php

namespace ContinuousPipe\Billing\Plan\Repository;

use ContinuousPipe\Billing\Plan\AddOn;
use ContinuousPipe\Billing\Plan\Plan;
use ContinuousPipe\Billing\Plan\PlanNotFound;
use JMS\Serializer\SerializerInterface;

class JsonRepresentedPlanRepository implements PlanRepository
{
    /**
     * @var Plan[]
     */
    private $plans;

    /**
     * @var AddOn[]
     */
    private $addOns;

    public function __construct(SerializerInterface $serializer, array $plansAsJson, array $addOnsAsJson)
    {
        $this->plans = $serializer->deserialize(
            \GuzzleHttp\json_encode($plansAsJson),
            'array<'.Plan::class.'>',
            'json'
        );

        $this->addOns = $serializer->deserialize(
            \GuzzleHttp\json_encode($addOnsAsJson),
            'array<'.AddOn::class.'>',
            'json'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findPlans(): array
    {
        return $this->plans;
    }

    /**
     * {@inheritdoc}
     */
    public function findAddOns(): array
    {
        return $this->addOns;
    }

    public function findPlanByIdentifier(string $identifier): Plan
    {
        foreach ($this->plans as $plan) {
            if ($plan->getIdentifier() == $identifier) {
                return $plan;
            }
        }

        throw new PlanNotFound(sprintf('Plan with identifier "%s" not found', $identifier));
    }
}
