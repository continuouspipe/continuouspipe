<?php

namespace ContinuousPipe\UserActivity;

interface UserActivityDispatcher
{
    public function dispatch(UserActivity $userActivity);
}
