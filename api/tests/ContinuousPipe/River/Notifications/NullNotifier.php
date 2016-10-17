<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class NullNotifier implements Notifier
{
    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return true;
    }
}
