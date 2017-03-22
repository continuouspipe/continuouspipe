<?php

namespace ContinuousPipe\Billing\ActivityTracker;

use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Team\Team;

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
     * @param Team $team
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     *
     * @return UserActivity[]
     */
    public function findBy(Team $team, \DateTimeInterface $start, \DateTimeInterface $end) : array;


    /**
     * Count how many user activity occurred during the given time period.
     *
     * @param Team $team
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     *
     * @return int
     */
    public function countEventsBy(Team $team, \DateTimeInterface $start, \DateTimeInterface $end) : int;
}
