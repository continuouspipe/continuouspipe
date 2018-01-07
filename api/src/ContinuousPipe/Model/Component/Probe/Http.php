<?php

namespace ContinuousPipe\Model\Component\Probe;

use ContinuousPipe\Model\Component\Probe;

class Http extends Probe
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $scheme;

    /**
     * Http constructor.
     *
     * @param string $path
     * @param int    $port
     * @param string $host
     * @param string $scheme
     * @param int    $initialDelaySeconds
     * @param int    $timeoutSeconds
     * @param int    $periodSeconds
     * @param int    $successThreshold
     * @param int    $failureThreshold
     */
    public function __construct($path, $port, $host = null, $scheme = null, $initialDelaySeconds = null, $timeoutSeconds = null, $periodSeconds = null, $successThreshold = null, $failureThreshold = null)
    {
        parent::__construct($initialDelaySeconds, $timeoutSeconds, $periodSeconds, $successThreshold, $failureThreshold);

        $this->path = $path;
        $this->port = $port;
        $this->host = $host;
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }
}
