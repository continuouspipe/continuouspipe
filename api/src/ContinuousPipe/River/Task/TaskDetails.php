<?php

namespace ContinuousPipe\River\Task;

class TaskDetails
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $logId;

    /**
     * @param string $identifier
     * @param string $logId
     */
    public function __construct($identifier, $logId)
    {
        $this->identifier = $identifier;
        $this->logId = $logId;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }
}
