<?php

namespace ContinuousPipe\Billing\Plan;

use JMS\Serializer\Annotation as JMS;

class Plan
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $identifier;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("float")
     *
     * @var float
     */
    private $price;

    /**
     * @JMS\Type("ContinuousPipe\Billing\Plan\Metrics")
     *
     * @var Metrics
     */
    private $metrics;

    public function __construct(
        string $identifier,
        string $name,
        float $price,
        Metrics $metrics
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->price = $price;
        $this->metrics = $metrics;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return Metrics
     */
    public function getMetrics(): Metrics
    {
        return $this->metrics;
    }
}
