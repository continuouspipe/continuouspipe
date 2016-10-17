<?php

namespace ContinuousPipe\River\Tide\Status;

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
     * @param string $state
     * @param string $description
     * @param string $url
     */
    public function __construct($state, $description = null, $url = null)
    {
        $this->state = $state;
        $this->description = $description;
        $this->url = $url;
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
}
