<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\UserActivity\MiddlewareSupportingUserActivityDispatcher;
use ContinuousPipe\UserActivity\TraceableUserActivityDispatcher;
use Ramsey\Uuid\Uuid;

class UserActivityContext implements Context
{
    /**
     * @var TraceableUserActivityDispatcher
     */
    private $traceableUserActivityDispatcher;
    /**
     * @var MiddlewareSupportingUserActivityDispatcher
     */
    private $middlewareSupportingUserActivityDispatcher;

    public function __construct(
        TraceableUserActivityDispatcher $traceableUserActivityDispatcher,
        MiddlewareSupportingUserActivityDispatcher $middlewareSupportingUserActivityDispatcher
    ) {
        $this->traceableUserActivityDispatcher = $traceableUserActivityDispatcher;
        $this->middlewareSupportingUserActivityDispatcher = $middlewareSupportingUserActivityDispatcher;
    }

    /**
     * @Then the commit activity of the user :username on the flow :flowUuid should have been dispatched
     */
    public function theCommitActivityOfTheUserOnTheFlowShouldHaveBeenDispatched($username, $flowUuid)
    {
        foreach ($this->traceableUserActivityDispatcher->getDispatchedActivity() as $activity) {
            if ($activity->getFlowUuid()->equals(Uuid::fromString($flowUuid)) && $activity->getUser()->getUsername() == $username) {
                return;
            }
        }

        throw new \RuntimeException(sprintf('Activity for the user %s on flow %s not found', $username, $flowUuid));
    }

    /**
     * @Given the commit activity dispatcher will fail
     */
    public function theCommitActivityDispatcherWillFail()
    {
        $this->middlewareSupportingUserActivityDispatcher->pushMiddleware(function() {
            throw new \RuntimeException('The dispatch failed');
        });
    }
}
