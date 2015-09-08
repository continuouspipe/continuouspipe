<?php

namespace ContinuousPipe\Runner\Client\Notification;

use JMS\Serializer\Annotation as JMS;

class CommandResult
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $command;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $exitCode;

    /**
     * @param string $command
     * @param int    $exitCode
     */
    public function __construct($command, $exitCode)
    {
        $this->command = $command;
        $this->exitCode = $exitCode;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
