<?php

namespace ContinuousPipe\Builder;

class Notification
{
    /**
     * @var HttpNotification
     */
    private $http;

    /**
     * @param HttpNotification $httpNotification
     *
     * @return Notification
     */
    public static function withHttp(HttpNotification $httpNotification)
    {
        $notification = new self();
        $notification->http = $httpNotification;

        return $notification;
    }

    /**
     * @return HttpNotification
     */
    public function getHttp()
    {
        return $this->http;
    }
}
