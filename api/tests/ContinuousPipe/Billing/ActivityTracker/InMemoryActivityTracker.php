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
}
