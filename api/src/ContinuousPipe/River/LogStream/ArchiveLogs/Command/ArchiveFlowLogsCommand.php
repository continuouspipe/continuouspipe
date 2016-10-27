<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Command;

use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class ArchiveFlowLogsCommand
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @param Uuid $flowUuid
     */
    public function __construct(Uuid $flowUuid)
    {
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }
}
