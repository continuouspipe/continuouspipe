<?php

namespace ContinuousPipe\Pipe\Environment;

class PublicEndpoint
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var array|PublicEndpointPort[]
     */
    private $ports = [];

    /**
     * @param string               $name
     * @param string               $address
     * @param PublicEndpointPort[] $ports
     */
    public function __construct(string $name, string $address, array $ports = [])
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
     * @return array|PublicEndpointPort[]
     */
    public function getPorts()
    {
        return $this->ports;
    }
}
