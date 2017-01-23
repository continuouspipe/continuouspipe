<?php

namespace ContinuousPipe\Billing\ActivityTracker\EventListener;

use ContinuousPipe\Billing\ActivityTracker\ActivityTracker;
use ContinuousPipe\Message\UserActivity;

class StoreLastUserActivity
{
    /**
     * @var ActivityTracker
     */
    private $activityTracker;

    /**
     * @param ActivityTracker $activityTracker
     */
    public function __construct(ActivityTracker $activityTracker)
    {
        $this->activityTracker = $activityTracker;
    }

    public function notify(UserActivity $event)
    {
        $this->activityTracker->track($event);
    }
}
