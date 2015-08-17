<?php

namespace ContinuousPipe\Adapter\Kubernetes;

class Cluster
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $address
     * @param string $version
     */
    public function __construct($address, $version)
    {
        $this->address = $address;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
