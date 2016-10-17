<?php

namespace ContinuousPipe\River\Notifications;

use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

interface Notifier
{
    /**
     * @param Tide   $tide
     * @param Status $status
     * @param array  $configuration
     *
     * @throws NotificationException
     */
    public function notify(Tide $tide, Status $status, array $configuration);

    /**
     * @param Tide   $tide
     * @param Status $status
     * @param array  $configuration
     *
     * @return bool
     */
    public function supports(Tide $tide, Status $status, array $configuration);
}
