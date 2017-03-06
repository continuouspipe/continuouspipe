<?php

namespace ContinuousPipe\River\Task\Wait;

use ContinuousPipe\River\Task\Wait\Configuration\Status;
use JMS\Serializer\Annotation as JMS;

class WaitTaskConfiguration
{
    /**
     * @JMS\Type("ContinuousPipe\River\Task\Wait\Configuration\Status")
     *
     * @var Status
     */
    private $status;

    /**
     * @param Status $status
     */
    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
