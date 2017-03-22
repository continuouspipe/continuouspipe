<?php

namespace ContinuousPipe\Billing\ActivityTracker;

use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Team\Team;

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
    public function findBy(Team $team, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->decoratedTracker->findBy($team, $start, $end);
    }

    /**
     * Count how many user activity occurred during the given time period.
     *
     * @param Team $team
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     *
     * @return int
     */
    public function countEventsBy(Team $team, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return $this->decoratedTracker->countEventsBy($team, $start, $end);
    }

    /**
     * @return UserActivity[]
     */
    public function getTracked(): array
    {
        return $this->tracked;
    }
}
