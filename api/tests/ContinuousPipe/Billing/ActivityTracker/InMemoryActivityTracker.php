<?php

namespace ContinuousPipe\Billing\ActivityTracker;

use ContinuousPipe\Message\UserActivity;
use Ramsey\Uuid\UuidInterface;

class InMemoryActivityTracker implements ActivityTracker
{
    /**
     * @var UserActivity[]
     */
    private $activities = [];

    /**
     * {@inheritdoc}
     */
    public function track(UserActivity $userActivity)
    {
        $this->activities[] = $userActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(UuidInterface $flowUuid, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return array_values(array_filter($this->activities, function(UserActivity $activity) use ($flowUuid, $start, $end) {
            return $activity->getFlowUuid()->equals($flowUuid) &&
                $activity->getDateTime() >= $start &&
                $activity->getDateTime() <= $end;
        }));
    }
}
