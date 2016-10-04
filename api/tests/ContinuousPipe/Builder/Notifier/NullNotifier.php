<?php

namespace ContinuousPipe\Builder\Notifier;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Notification;
use ContinuousPipe\Builder\Notifier;

class NullNotifier implements Notifier
{
    /**
     * {@inheritdoc}
     */
    public function notify(Notification $notification, Build $build)
    {
    }
}
