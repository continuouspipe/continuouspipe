<?php

namespace ContinuousPipe\Model\Component;

use ContinuousPipe\Model\Component\Status\ContainerStatus;

class Status implements \ContinuousPipe\Model\Status
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $publicEndpoints = [];

    /**
     * @var array|Status\ContainerStatus[]
     */
    private $containers = [];

    /**
     * @param string $status
     * @param array $publicEndpoints
     * @param ContainerStatus[] $containers
     */
    public function __construct($status, array $publicEndpoints = [], array $containers = [])
    {
        $this->status = $status;
        $this->publicEndpoints = $publicEndpoints;
        $this->containers = $containers;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getPublicEndpoints()
    {
        return $this->publicEndpoints;
    }

    /**
     * @return array|Status\ContainerStatus[]
     */
    public function getContainers()
    {
        return $this->containers ?: [];
    }
}
