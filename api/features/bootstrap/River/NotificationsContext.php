<?php

namespace River;

use Behat\Behat\Context\Context;
use ContinuousPipe\River\Notifications\TraceableNotifier;
use ContinuousPipe\River\Tide\Status\Status;

class NotificationsContext implements Context
{
    /**
     * @var TraceableNotifier
     */
    private $traceableSlackNotifier;

    /**
     * @param TraceableNotifier $traceableSlackNotifier
     */
    public function __construct(TraceableNotifier $traceableSlackNotifier)
    {
        $this->traceableSlackNotifier = $traceableSlackNotifier;
    }

    /**
     * @Then a Slack :type notification should have been sent
     */
    public function aSlackNotificationContainingShouldHaveBeenSent($type)
    {
        if (count($this->getMatchingNotifications($type)) == 0) {
            throw new \RuntimeException('No matching notification found');
        }
    }

    /**
     * @Then a Slack :type notification should not have been sent
     */
    public function aSlackNotificationContainingShouldNotHaveBeenSent($type)
    {
        if (count($this->getMatchingNotifications($type)) != 0) {
            throw new \RuntimeException('Matching notification found');
        }
    }

    /**
     * @param string $type
     *
     * @return array
     */
    private function getMatchingNotifications($type)
    {
        return array_filter($this->traceableSlackNotifier->getNotifications(), function(Status $notification) use ($type) {
            return $notification->getState() == $type;
        });
    }
}
