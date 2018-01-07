<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\View\Deployment;

class NullNotifier implements Notifier
{
    /**
     * {@inheritdoc}
     */
    public function notify($address, Deployment $deployment)
    {
    }
}
