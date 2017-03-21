<?php

namespace ContinuousPipe\Billing\ActivityTracker;

use ContinuousPipe\Message\UserActivity;
use ContinuousPipe\Security\Team\Team;
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
    public function findBy(Team $team, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return array_values(array_filter($this->activities, function(UserActivity $activity) use ($team, $start, $end) {
            return $activity->getTeamSlug() == $team->getSlug() &&
                $activity->getDateTime() >= $start &&
                $activity->getDateTime() <= $end;
        }));
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
        return count(array_filter($this->activities, function(UserActivity $activity) use ($team, $start, $end) {
            return $activity->getTeamSlug() == $team->getSlug() &&
                $activity->getDateTime() >= $start &&
                $activity->getDateTime() <= $end;
        }));
    }
}
