<?php

namespace ContinuousPipe\Runner\Client;

use JMS\Serializer\Annotation as JMS;

class RunRequest
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $image;

    /**
     * @JMS\Type("array<string, string>")
     * @JMS\SerializedName("env")
     *
     * @var array
     */
    private $environmentVariables;

    /**
     * @JMS\Type("array<string>")
     *
     * @var array
     */
    private $commands;

    /**
     * @JMS\Type("ContinuousPipe\Runner\Client\Logging")
     *
     * @var Logging
     */
    private $logging;

    /**
     * @JMS\Type("ContinuousPipe\Runner\Client\Notification")
     *
     * @var Notification
     */
    private $notification;

    /**
     * @param string       $image
     * @param array        $environmentVariables
     * @param array        $commands
     * @param Logging      $logging
     * @param Notification $notification
     */
    public function __construct($image, array $environmentVariables, array $commands, Logging $logging, Notification $notification)
    {
        $this->image = $image;
        $this->environmentVariables = $environmentVariables;
        $this->commands = $commands;
        $this->logging = $logging;
        $this->notification = $notification;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return array
     */
    public function getEnvironmentVariables()
    {
        return $this->environmentVariables;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @return Logging
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
