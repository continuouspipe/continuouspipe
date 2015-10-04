<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Pipe\Tests\Notification\TraceableNotifier;
use ContinuousPipe\Pipe\View\Deployment;

class NotificationContext implements Context
{
    /**
     * @var TraceableNotifier
     */
    private $notifier;

    /**
     * @param TraceableNotifier $notifier
     */
    public function __construct(TraceableNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * @Then a notification should be sent back
     */
    public function aNotificationShouldBeSentBack()
    {
        $this->getNotification();
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
                var_export($value)
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
                var_export($value)
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
        $notifications = $this->notifier->getNotifications();
        if (0 == count($notifications)) {
            throw new \RuntimeException('Expecting 1 or more notifications, found 0');
        }

        return current($notifications);
    }
}