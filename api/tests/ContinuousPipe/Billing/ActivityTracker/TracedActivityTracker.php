<?php

namespace ContinuousPipe\Billing\ActivityTracker;

use ContinuousPipe\Message\UserActivity;
use Ramsey\Uuid\UuidInterface;

class TracedActivityTracker implements ActivityTracker
{
    /**
     * @var ActivityTracker
     */
    private $decoratedTracker;

    /**
     * @var UserActivity[]
     */
    private $tracked = [];

    /**
     * @param ActivityTracker $decoratedTracker
     */
    public function __construct(ActivityTracker $decoratedTracker)
    {
        $this->decoratedTracker = $decoratedTracker;
    }

    /**
     * {@inheritdoc}
     */
    public function track(UserActivity $userActivity)
    {
        $this->decoratedTracker->track($userActivity);

        $this->tracked[] = $userActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(UuidInterface $flowUuid, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->decoratedTracker->findBy($flowUuid, $start, $end);
    }

    /**
     * @return UserActivity[]
     */
    public function getTracked(): array
    {
        return $this->tracked;
    }
}
