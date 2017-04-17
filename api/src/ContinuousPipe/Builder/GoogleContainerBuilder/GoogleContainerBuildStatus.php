<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use JMS\Serializer\Annotation as JMS;

class GoogleContainerBuildStatus
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $reason;

    /**
     * @param string $status
     * @param string $reason
     */
    public function __construct(string $status, string $reason)
    {
        $this->status = $status;
        $this->reason = $reason;
    }

    public function isSuccessful() : bool
    {
        return 'SUCCESS' == $this->status;
    }

    public function isRunning() : bool
    {
        return in_array($this->status, ['QUEUED', 'WORKING']);
    }
}
