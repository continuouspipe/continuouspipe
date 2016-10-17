<?php

namespace ContinuousPipe\River\Tide\Status;

use ContinuousPipe\Pipe\Client\PublicEndpoint;

final class Status
{
    const STATE_SUCCESS = 'success';
    const STATE_FAILURE = 'failure';
    const STATE_PENDING = 'pending';
    const STATE_RUNNING = 'running';
    const STATE_UNKNOWN = 'unknown';

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $description;

    /**
     * @var null|string
     */
    private $url;

    /**
     * @var PublicEndpoint[]
     */
    private $publicEndpoints;

    /**
     * @param string           $state
     * @param string           $description
     * @param string           $url
     * @param PublicEndpoint[] $publicEndpoints
     */
    public function __construct($state, $description = null, $url = null, array $publicEndpoints = [])
    {
        $this->state = $state;
        $this->description = $description;
        $this->url = $url;
        $this->publicEndpoints = $publicEndpoints;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return PublicEndpoint[]
     */
    public function getPublicEndpoints()
    {
        return $this->publicEndpoints;
    }
}
