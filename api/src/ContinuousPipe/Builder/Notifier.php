<?php

namespace ContinuousPipe\Builder;

interface Notifier
{
    /**
     * @param Notification $notification
     * @param Build        $build
     */
    public function notify(Notification $notification, Build $build);
}
