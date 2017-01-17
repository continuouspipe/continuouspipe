<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Activity\TraceableUserActivityDispatcher;
use Ramsey\Uuid\Uuid;

class UserActivityContext implements Context
{
    /**
     * @var TraceableUserActivityDispatcher
     */
    private $traceableUserActivityDispatcher;

    public function __construct(TraceableUserActivityDispatcher $traceableUserActivityDispatcher)
    {
        $this->traceableUserActivityDispatcher = $traceableUserActivityDispatcher;
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
}
