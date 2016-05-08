<?php

namespace ContinuousPipe\River\Task;

use JMS\Serializer\Annotation as JMS;

class TaskDetails
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $identifier;

    /**
     * @JMS\Type("string")
     *
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
