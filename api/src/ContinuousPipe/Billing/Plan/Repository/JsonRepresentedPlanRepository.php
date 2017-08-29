<?php

namespace ContinuousPipe\Billing\Plan\Repository;

use ContinuousPipe\Billing\Plan\AddOn;
use ContinuousPipe\Billing\Plan\Plan;
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
}
