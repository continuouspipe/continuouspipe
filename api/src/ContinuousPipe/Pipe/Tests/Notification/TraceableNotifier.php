<?php

namespace ContinuousPipe\Pipe\Tests\Notification;

use ContinuousPipe\Pipe\Notification\Notifier;
use ContinuousPipe\Pipe\View\Deployment;

class TraceableNotifier implements Notifier
{
    /**
     * @var array
     */
    private $notifications = [];

    /**
     * {@inheritdoc}
     */
    public function notify($address, Deployment $deployment)
    {
        var_dump('notify', $deployment);
        $this->notifications[] = [
            'address' => $address,
            'deployment' => $deployment,
        ];
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
