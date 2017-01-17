<?php

namespace ContinuousPipe\Activity;

use ContinuousPipe\UserActivity\UserActivity;
use ContinuousPipe\UserActivity\UserActivityDispatcher;

class InMemoryDispatcher implements UserActivityDispatcher
{
    private $activity = [];

    public function dispatch(UserActivity $userActivity)
    {
        $this->activity[] = $userActivity;
    }
}
