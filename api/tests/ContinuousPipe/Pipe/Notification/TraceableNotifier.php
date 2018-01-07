<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\Notification\Notifier;
use ContinuousPipe\Pipe\View\Deployment;

class TraceableNotifier implements Notifier
{
    /**
     * @var Notifier
     */
    private $decoratedNotifier;

    /**
     * @var array
     */
    private $notifications = [];

    /**
     * @param Notifier $decoratedNotifier
     */
    public function __construct(Notifier $decoratedNotifier)
    {
        $this->decoratedNotifier = $decoratedNotifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notify($address, Deployment $deployment)
    {
        $this->decoratedNotifier->notify($address, $deployment);

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
