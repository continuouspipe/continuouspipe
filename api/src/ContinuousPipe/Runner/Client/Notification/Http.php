<?php

namespace ContinuousPipe\Runner\Client\Notification;

use JMS\Serializer\Annotation as JMS;

class Http
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $address;

    /**
     * @param string $address
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
