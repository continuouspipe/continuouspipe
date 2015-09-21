<?php

namespace ContinuousPipe\River\Task\Build\Command;

use ContinuousPipe\Builder\Request\BuildRequest;
use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class BuildImageCommand
{
    /**
     * @JMS\Type("ContinuousPipe\Builder\Request\BuildRequest")
     *
     * @var BuildRequest
     */
    private $buildRequest;

    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $tideUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $logId;

    /**
     * @param Uuid         $tideUuid
     * @param BuildRequest $buildRequest
     * @param string       $logId
     */
    public function __construct(Uuid $tideUuid, BuildRequest $buildRequest, $logId)
    {
        $this->buildRequest = $buildRequest;
        $this->tideUuid = $tideUuid;
        $this->logId = $logId;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return BuildRequest
     */
    public function getBuildRequest()
    {
        return $this->buildRequest;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }
}
