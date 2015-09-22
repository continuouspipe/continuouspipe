<?php

namespace ContinuousPipe\River\Task\Build\Command;

use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class BuildImagesCommand
{
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
     * @JMS\Type("array<string,string>")
     *
     * @var array
     */
    private $buildEnvironment;

    /**
     * @param Uuid   $tideUuid
     * @param array  $buildEnvironment
     * @param string $logId
     */
    public function __construct(Uuid $tideUuid, array $buildEnvironment, $logId)
    {
        $this->tideUuid = $tideUuid;
        $this->logId = $logId;
        $this->buildEnvironment = $buildEnvironment;
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * @return array
     */
    public function getBuildEnvironment()
    {
        return $this->buildEnvironment;
    }
}
