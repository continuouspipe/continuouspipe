<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Pipe\Notification\HookableNotifier;
use ContinuousPipe\Pipe\Notification\NotificationException;
use ContinuousPipe\Pipe\Notification\TraceableNotifier;
use ContinuousPipe\Pipe\View\Deployment;

class NotificationContext implements Context
{
    /**
     * @var TraceableNotifier
     */
    private $traceableNotifier;
    /**
     * @var HookableNotifier
     */
    private $hookableNotifier;

    /**
     * @param TraceableNotifier $traceableNotifier
     * @param HookableNotifier $hookableNotifier
     */
    public function __construct(TraceableNotifier $traceableNotifier, HookableNotifier $hookableNotifier)
    {
        $this->traceableNotifier = $traceableNotifier;
        $this->hookableNotifier = $hookableNotifier;
    }

    /**
     * @Given the first notification will fail
     */
    public function theFirstNotificationWillFail()
    {
        $count = 0;
        $this->hookableNotifier->addHook(function() use (&$count) {
            if ($count++ == 0) {
                throw new NotificationException('It failed, yay!');
            }
        });
    }

    /**
     * @Then one notification should be sent back
     * @Then one notification should be successfully sent
     */
    public function oneNotificationShouldBeSentBack()
    {
        $notifications = $this->traceableNotifier->getNotifications();
        if (1 != count($notifications)) {
            throw new \RuntimeException(sprintf('Expecting 1 notifications, found %s', count($notifications)));
        }
    }

    /**
     * @Then the notification should contain the status of the component :component
     */
    public function theNotificationShouldContainTheStatusOfTheComponent($component)
    {
        $this->getDeploymentStatusOfComponent($component);
    }

    /**
     * @Then the deployment status :status of the component :component should be true
     */
    public function theDeploymentStatusOfTheComponentShouldBeTrue($component, $status)
    {
        $deploymentStatus = $this->getDeploymentStatusOfComponent($component);
        $method = 'is'.lcfirst($status);
        $value = $deploymentStatus->$method();

        if ($value !== true) {
            throw new \RuntimeException(sprintf(
                'Expected value to be true but got %s',
                var_export($value, true)
            ));
        }
    }

    /**
     * @Then the deployment status :status of the component :component should be false
     */
    public function theDeploymentStatusOfTheComponentShouldBeFalse($component, $status)
    {
        $deploymentStatus = $this->getDeploymentStatusOfComponent($component);
        $method = 'is'.lcfirst($status);
        $value = $deploymentStatus->$method();

        if ($value !== false) {
            throw new \RuntimeException(sprintf(
                'Expected value to be true but got %s',
                var_export($value, true)
            ));
        }
    }

    /**
     * @param string $component
     * @return \ContinuousPipe\Pipe\View\ComponentStatus
     */
    private function getDeploymentStatusOfComponent($component)
    {
        /** @var Deployment $deployment */
        $deployment = $this->getNotification()['deployment'];
        $statuses = $deployment->getComponentStatuses();

        if (!array_key_exists($component, $statuses)) {
            throw new \RuntimeException(sprintf(
                'No status for component "%s" found.',
                $component
            ));
        }

        return $statuses[$component];
    }

    /**
     * @return array
     */
    private function getNotification()
    {
        $notifications = $this->traceableNotifier->getNotifications();
        if (0 == count($notifications)) {
            throw new \RuntimeException('Expecting 1 or more notifications, found 0');
        }

        return current($notifications);
    }
}
