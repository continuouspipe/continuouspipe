<?php

namespace ContinuousPipe\Billing\Subscription;

class Subscription
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $plan;

    /**
     * From Recurly, used at the moment:
     *  - active
     *  - canceled
     *  - expired
     *  - future
     *  - in_trial
     *  - live
     *  - past_due
     *
     * @var string
     */
    private $state;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var \DateTimeInterface|null
     */
    private $expirationDate;

    /**
     * @var \DateTimeInterface
     */
    private $currentBillingPeriodStartedAt;

    /**
     * @var |DateTimeInterface
     */
    private $currentBillingPeriodEndsAt;

    /**
     * @var int
     */
    private $unitAmountInCents;

    /**
     * @param string $uuid
     * @param string $plan
     * @param string $state
     * @param int $quantity
     * @param int $unitAmountInCents
     * @param \DateTimeInterface $currentBillingPeriodStartedAt
     * @param \DateTimeInterface $currentBillingPeriodEndsAt
     * @param \DateTimeInterface $expirationDate
     */
    public function __construct(
        string $uuid,
        string $plan,
        string $state,
        int $quantity,
        int $unitAmountInCents,
        \DateTimeInterface $currentBillingPeriodStartedAt,
        \DateTimeInterface $currentBillingPeriodEndsAt,
        \DateTimeInterface $expirationDate = null
    ) {
        $this->uuid = $uuid;
        $this->plan = $plan;
        $this->state = $state;
        $this->quantity = $quantity;
        $this->unitAmountInCents = $unitAmountInCents;
        $this->currentBillingPeriodStartedAt = $currentBillingPeriodStartedAt;
        $this->currentBillingPeriodEndsAt = $currentBillingPeriodEndsAt;
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getPlan(): string
    {
        return $this->plan;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCurrentBillingPeriodStartedAt(): \DateTimeInterface
    {
        return $this->currentBillingPeriodStartedAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCurrentBillingPeriodEndsAt(): \DateTimeInterface
    {
        return $this->currentBillingPeriodEndsAt;
    }

    /**
     * @return int
     */
    public function getUnitAmountInCents(): int
    {
        return $this->unitAmountInCents;
    }
}
