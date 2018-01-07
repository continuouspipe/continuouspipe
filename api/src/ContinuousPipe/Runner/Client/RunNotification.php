<?php

namespace ContinuousPipe\Runner\Client;

use ContinuousPipe\Runner\Client\Notification\CommandResult;
use JMS\Serializer\Annotation as JMS;

class RunNotification
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_QUEUED = 'queued';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("array<ContinuousPipe\Runner\Client\Notification\CommandResult>")
     *
     * @var CommandResult[]
     */
    private $commands;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @param string $uuid
     * @param array  $commands
     * @param string $status
     */
    public function __construct($uuid, array $commands, $status)
    {
        $this->uuid = $uuid;
        $this->commands = $commands;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return Notification\CommandResult[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
