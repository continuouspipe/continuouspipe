<?php

namespace ContinuousPipe\River\Tide\Status;

final class Status
{
    const STATE_SUCCESS = 'success';
    const STATE_FAILURE = 'failure';
    const STATE_PENDING = 'pending';

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $state
     * @param string $description
     */
    public function __construct($state, $description = null)
    {
        $this->state = $state;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
