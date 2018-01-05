<?php

namespace ContinuousPipe\Model\Component;

class DeploymentStrategy
{
    /**
     * @var bool
     */
    private $attached;

    /**
     * @var bool
     */
    private $locked;

    /**
     * Should the component be reset first?
     *
     * @var bool
     */
    private $reset;

    /**
     * @var Probe
     */
    private $livenessProbe;

    /**
     * @var Probe
     */
    private $readinessProbe;

    /**
     * Maximum number of unavailable pods while deploying. This can be an integer or a percentage.
     *
     * @var string|null
     */
    private $maxUnavailable;

    /**
     * Maximum number of extra pods (in addition to the expected number of replicas) while deploying.
     * This can be an integer or a percentage.
     *
     * @var string|null
     */
    private $maxSurge;

    /**
     * @param Probe       $readinessProbe
     * @param Probe       $livenessProbe
     * @param bool        $attached
     * @param bool        $locked
     * @param bool        $reset
     * @param string|null $maxUnavailable
     * @param string|null $maxSurge
     */
    public function __construct(Probe $readinessProbe = null, Probe $livenessProbe = null, $attached = false, $locked = false, $reset = false, $maxUnavailable = null, $maxSurge = null)
    {
        $this->readinessProbe = $readinessProbe;
        $this->livenessProbe = $livenessProbe;
        $this->attached = $attached;
        $this->locked = $locked;
        $this->reset = $reset;
        $this->maxUnavailable = $maxUnavailable;
        $this->maxSurge = $maxSurge;
    }

    /**
     * @return bool
     */
    public function isAttached()
    {
        return $this->attached;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @return bool
     */
    public function isReset()
    {
        return $this->reset;
    }

    /**
     * @return Probe
     */
    public function getLivenessProbe()
    {
        return $this->livenessProbe;
    }

    /**
     * @return Probe
     */
    public function getReadinessProbe()
    {
        return $this->readinessProbe;
    }

    /**
     * @return string|null
     */
    public function getMaxUnavailable()
    {
        return $this->maxUnavailable;
    }

    /**
     * @return string|null
     */
    public function getMaxSurge()
    {
        return $this->maxSurge;
    }
}
