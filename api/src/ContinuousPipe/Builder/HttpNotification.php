<?php

namespace ContinuousPipe\Builder;

class HttpNotification
{
    /**
     * @var string
     */
    private $address;

    /**
     * @param string $address
     *
     * @return HttpNotification
     */
    public static function fromAddress($address)
    {
        $notification = new self();
        $notification->address = $address;

        return $notification;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
