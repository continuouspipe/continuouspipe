<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use JMS\Serializer\Annotation as JMS;

class GoogleContainerBuildStatus
{
    const SUCCESS = 'SUCCESS';
    const QUEUED = 'QUEUED';
    const WORKING = 'WORKING';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status;

    /**
     * @param string $status
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function isSuccessful() : bool
    {
        return self::SUCCESS == $this->status;
    }

    public function isRunning() : bool
    {
        return in_array($this->status, [self::QUEUED, self::WORKING]);
    }
}
