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
     * @param Uuid   $tideUuid
     * @param string $logId
     */
    public function __construct(Uuid $tideUuid, $logId)
    {
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
     * @return string
     */
    public function getLogId()
    {
        return $this->logId;
    }
}
