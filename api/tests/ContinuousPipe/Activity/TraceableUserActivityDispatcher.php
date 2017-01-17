<?php

namespace ContinuousPipe\Activity;

use ContinuousPipe\UserActivity\UserActivity;
use ContinuousPipe\UserActivity\UserActivityDispatcher;

class TraceableUserActivityDispatcher implements UserActivityDispatcher
{
    /**
     * @var UserActivityDispatcher
     */
    private $decoratedDispatcher;

    /**
     * @var UserActivity[]
     */
    private $dispatchedActivity = [];

    /**
     * @param UserActivityDispatcher $decoratedDispatcher
     */
    public function __construct(UserActivityDispatcher $decoratedDispatcher)
    {
        $this->decoratedDispatcher = $decoratedDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(UserActivity $userActivity)
    {
        $this->decoratedDispatcher->dispatch($userActivity);

        $this->dispatchedActivity[] = $userActivity;
    }

    /**
     * @return UserActivity[]
     */
    public function getDispatchedActivity(): array
    {
        return $this->dispatchedActivity;
    }
}
