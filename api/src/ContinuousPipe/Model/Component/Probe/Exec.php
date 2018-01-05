<?php

namespace ContinuousPipe\Model\Component\Probe;

use ContinuousPipe\Model\Component\Probe;

class Exec extends Probe
{
    /**
     * @var string[]
     */
    private $command;

    /**
     * @param string[] $command
     * @param int      $initialDelaySeconds
     * @param int      $timeoutSeconds
     * @param int      $periodSeconds
     * @param int      $successThreshold
     * @param int      $failureThreshold
     */
    public function __construct($command, $initialDelaySeconds = null, $timeoutSeconds = null, $periodSeconds = null, $successThreshold = null, $failureThreshold = null)
    {
        parent::__construct($initialDelaySeconds, $timeoutSeconds, $periodSeconds, $successThreshold);

        $this->command = $command;
    }

    /**
     * @return string[]
     */
    public function getCommand()
    {
        return $this->command;
    }
}
