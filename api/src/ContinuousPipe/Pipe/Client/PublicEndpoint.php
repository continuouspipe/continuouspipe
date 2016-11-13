<?php

namespace ContinuousPipe\Pipe\Client;

use JMS\Serializer\Annotation as JMS;

class PublicEndpoint
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $address;

    /**
     * @JMS\Type("array<ContinuousPipe\Pipe\Client\PublicEndpointPort>")
     *
     * @var array|PublicEndpointPort[]
     */
    private $ports;

    /**
     * @param string               $name
     * @param string               $address
     * @param PublicEndpointPort[] $ports
     */
    public function __construct($name, $address, array $ports = [])
    {
        $this->name = $name;
        $this->address = $address;
        $this->ports = $ports;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return PublicEndpointPort[]
     */
    public function getPorts()
    {
        return $this->ports;
    }
}
