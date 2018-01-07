<?php

namespace ContinuousPipe\Pipe\Client;

/**
 * @deprecated Duplicate of the `ContinuousPipe\Pipe\Environment\PublicEndpoint` object, after merging pipe.
 *             Kept to be compatible with serialized tides.
 */
class PublicEndpoint extends \ContinuousPipe\Pipe\Environment\PublicEndpoint
{
    private $name;
    private $address;
    private $ports;

    public function getName()
    {
        return $this->name ?? parent::getName();
    }

    public function getAddress()
    {
        return $this->address ?? parent::getAddress();
    }

    public function getPorts()
    {
        return $this->ports ?? parent::getPorts();
    }
}
