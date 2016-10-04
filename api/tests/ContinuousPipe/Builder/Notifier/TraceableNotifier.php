<?php

namespace ContinuousPipe\Builder\Notifier;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Notifier;

class TraceableNotifier implements Notifier
{
    private $notifications = [];
    /**
     * @var Notifier
     */
    private $decoratedNotifier;

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
    public function notify(Notification $notification, Build $build)
    {
        $this->decoratedNotifier->notify($notification, $build);

        $this->notifications[] = $notification;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
