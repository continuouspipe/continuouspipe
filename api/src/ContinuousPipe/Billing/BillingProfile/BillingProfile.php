<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Billing\Plan\Plan;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @deprecated Use `UserBillingProfile` instead
 */
class BillingProfile
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @JMS\Type("ContinuousPipe\Billing\Plan\Plan")
     *
     * @var Plan|null
     */
    private $plan;

    /**
     * @param UuidInterface $uuid
     * @param string $name
     * @param \DateTimeInterface $creationDate
     * @param Plan|null $plan
     */
    public function __construct(UuidInterface $uuid, string $name, \DateTimeInterface $creationDate, Plan $plan = null)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->creationDate = $creationDate;
        $this->plan = $plan;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    /**
     * @return Plan|null
     */
    public function getPlan()
    {
        return $this->plan;
    }
}
