<?php

namespace ContinuousPipe\River\Managed\Resources\Discrepancies;

use ContinuousPipe\River\Message\OperationalMessage;
use Ramsey\Uuid\UuidInterface;

class RepairResourcesDiscrepancies implements OperationalMessage
{
    /**
     * @var \DateTime
     */
    private $leftInterval;

    /**
     * @var \DateTime
     */
    private $rightInterval;

    /**
     * @var UuidInterface
     */
    private $flowUuid;

    public function __construct(\DateTime $leftInterval, \DateTime $rightInterval, UuidInterface $flowUuid)
    {
        $this->leftInterval = $leftInterval;
        $this->rightInterval = $rightInterval;
        $this->flowUuid = $flowUuid;
    }

    /**
     * @return \DateTime
     */
    public function getLeftInterval(): \DateTime
    {
        return $this->leftInterval;
    }

    /**
     * @return \DateTime
     */
    public function getRightInterval(): \DateTime
    {
        return $this->rightInterval;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
