<?php

namespace ContinuousPipe\Pipe\Notification;

use ContinuousPipe\Pipe\View\Deployment;

interface Notifier
{
    /**
     * Notifies about the deployment status.
     *
     * @param string     $address
     * @param Deployment $deployment
     */
    public function notify($address, Deployment $deployment);
}
