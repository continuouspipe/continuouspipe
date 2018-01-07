<?php

namespace ContinuousPipe\Model\Component\Status;

use ContinuousPipe\Model\Status;

class ContainerStatus implements Status
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $restartCount;

    /**
     * @param string $identifier
     * @param string $status
     * @param int $restartCount
     */
    public function __construct($identifier, $status, $restartCount)
    {
        $this->identifier = $identifier;
        $this->status = $status;
        $this->restartCount = $restartCount;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getRestartCount()
    {
        return $this->restartCount;
    }
}
