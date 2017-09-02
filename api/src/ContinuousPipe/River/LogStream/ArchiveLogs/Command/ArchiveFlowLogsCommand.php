<?php

namespace ContinuousPipe\River\LogStream\ArchiveLogs\Command;

use ContinuousPipe\Message\Message;
use ContinuousPipe\River\Message\OperationalMessage;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class ArchiveFlowLogsCommand implements OperationalMessage
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
