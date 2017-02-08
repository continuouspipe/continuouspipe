<?php

namespace ContinuousPipe\Billing\Usage;

use Ramsey\Uuid\UuidInterface;

interface UsageTracker
{
    /**
     * Get the usage of a given billing profile.
     *
     * @param UuidInterface $billingProfileUuid
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     *
     * @return Usage
     */
    public function getUsage(UuidInterface $billingProfileUuid, \DateTimeInterface $start, \DateTimeInterface $end) : Usage;
}
