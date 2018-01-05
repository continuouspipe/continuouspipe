<?php

namespace ContinuousPipe\Model\Component\Probe;

use ContinuousPipe\Model\Component\Probe;

class Tcp extends Probe
{
    /**
     * @var int
     */
    private $port;

    /**
     * @param int $port
     * @param int $initialDelaySeconds
     * @param int $timeoutSeconds
     * @param int $periodSeconds
     * @param int $successThreshold
     * @param int $failureThreshold
     */
    public function __construct($port, $initialDelaySeconds = null, $timeoutSeconds = null, $periodSeconds = null, $successThreshold = null, $failureThreshold = null)
    {
        parent::__construct($initialDelaySeconds, $timeoutSeconds, $periodSeconds, $successThreshold);

        $this->port = $port;
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }
}
