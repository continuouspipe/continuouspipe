<?php

namespace ContinuousPipe\UserActivity;

class MiddlewareSupportingUserActivityDispatcher implements UserActivityDispatcher
{
    /**
     * @var UserActivityDispatcher
     */
    private $decoratedDispatcher;

    /**
     * @var callable[]
     */
    private $stack = [];

    public function __construct(UserActivityDispatcher $decoratedDispatcher)
    {
        $this->decoratedDispatcher = $decoratedDispatcher;
    }

    public function dispatch(UserActivity $userActivity)
    {
        $previous = function (UserActivity $userActivity) {
            return $this->decoratedDispatcher->dispatch($userActivity);
        };

        foreach ($this->stack as $middleware) {
            $middleware($userActivity, $previous);
        }

        if (count($this->stack) == 0) {
            $previous($userActivity);
        }
    }

    public function pushMiddleware(callable $middleware)
    {
        $this->stack[] = $middleware;
    }
}
