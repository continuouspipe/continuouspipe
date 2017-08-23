<?php

namespace ContinuousPipe\River\Tide\Status;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use JMS\Serializer\Annotation as JMS;

final class Status
{
    const STATE_SUCCESS = 'success';
    const STATE_FAILURE = 'failure';
    const STATE_PENDING = 'pending';
    const STATE_RUNNING = 'running';
    const STATE_UNKNOWN = 'unknown';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $state;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("string")
     *
     * @var null|string
     */
    private $url;

    /**
     * @JMS\Type("array<ContinuousPipe\Pipe\Client\PublicEndpoint>")
     *
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

    public function withDescription(string $description) : self
    {
        $status = clone $this;
        $status->description = $description;

        return $status;
    }
}
