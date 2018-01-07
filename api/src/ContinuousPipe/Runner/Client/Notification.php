<?php

namespace ContinuousPipe\Runner\Client;

use ContinuousPipe\Runner\Client\Notification\Http;
use JMS\Serializer\Annotation as JMS;

class Notification
{
    /**
     * @JMS\Type("ContinuousPipe\Runner\Client\Notification\Http")
     *
     * @var Http
     */
    private $http;

    /**
     * @param Http $http
     */
    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    /**
     * @return Http
     */
    public function getHttp()
    {
        return $this->http;
    }
}
