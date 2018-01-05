<?php

namespace ContinuousPipe\Model\Component;

abstract class Probe
{
    /**
     * @var int
     */
    private $initialDelaySeconds;

    /**
     * @var int
     */
    private $timeoutSeconds;

    /**
     * @var int
     */
    private $periodSeconds;

    /**
     * @var int
     */
    private $successThreshold;

    /**
     * @var int
     */
    private $failureThreshold;

    /**
     * @param int $initialDelaySeconds
     * @param int $timeoutSeconds
     * @param int $periodSeconds
     * @param int $successThreshold
     * @param int $failureThreshold
     */
    public function __construct($initialDelaySeconds = null, $timeoutSeconds = null, $periodSeconds = null, $successThreshold = null, $failureThreshold = null)
    {
        $this->initialDelaySeconds = $initialDelaySeconds;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->periodSeconds = $periodSeconds;
        $this->successThreshold = $successThreshold;
        $this->failureThreshold = $failureThreshold;
    }

    /**
     * @return int
     */
    public function getInitialDelaySeconds()
    {
        return $this->initialDelaySeconds;
    }

    /**
     * @return int
     */
    public function getTimeoutSeconds()
    {
        return $this->timeoutSeconds;
    }

    /**
     * @return int
     */
    public function getPeriodSeconds()
    {
        return $this->periodSeconds;
    }

    /**
     * @return int
     */
    public function getSuccessThreshold()
    {
        return $this->successThreshold;
    }

    /**
     * @return int
     */
    public function getFailureThreshold()
    {
        return $this->failureThreshold;
    }
}
