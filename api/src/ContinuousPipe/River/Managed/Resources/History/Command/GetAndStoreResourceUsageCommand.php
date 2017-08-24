<?php

namespace ContinuousPipe\River\Managed\Resources\History\Command;

use Ramsey\Uuid\UuidInterface;

class GetAndStoreResourceUsageCommand
{
    /**
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * @param UuidInterface $flowUuid
     */
    public function __construct(UuidInterface $flowUuid)
    {
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
