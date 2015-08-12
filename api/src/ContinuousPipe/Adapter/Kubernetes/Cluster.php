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
