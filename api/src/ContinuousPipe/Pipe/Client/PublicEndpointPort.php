<?php

namespace ContinuousPipe\Pipe\Client;

/**
 * @deprecated Duplicate of the `ContinuousPipe\Pipe\Environment\PublicEndpoint` object, after merging pipe.
 *             Kept to be compatible with serialized tides.
 */
class PublicEndpointPort extends \ContinuousPipe\Pipe\Environment\PublicEndpointPort
{
    private $number;
    private $protocol;

    public function getNumber()
    {
        return $this->number ?? parent::getNumber();
    }

    public function getProtocol()
    {
        return $this->protocol ?? parent::getProtocol();
    }
}
