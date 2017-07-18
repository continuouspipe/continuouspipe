<?php

namespace ContinuousPipe\River\Flex\AsFeature\Command;

use ContinuousPipe\River\Command\FlowCommand;
use Ramsey\Uuid\UuidInterface;

class ActivateFlex implements FlowCommand
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
     * {@inheritdoc}
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
