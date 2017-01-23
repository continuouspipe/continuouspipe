<?php

namespace ContinuousPipe\Billing\ActivityTracker;

use ContinuousPipe\Message\UserActivity;
use Ramsey\Uuid\UuidInterface;

interface ActivityTracker
{
    /**
     * Track the user activity.
     *
     * @param UserActivity $userActivity
     */
    public function track(UserActivity $userActivity);

    /**
     * Find the user activity for this flow between start and end ranges.
     *
     * @param UuidInterface $flowUuid
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     *
     * @return UserActivity[]
     */
    public function findBy(UuidInterface $flowUuid, \DateTimeInterface $start, \DateTimeInterface $end) : array;
}
