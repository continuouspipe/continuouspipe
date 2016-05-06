<?php

namespace ContinuousPipe\River\Tide\Concurrency\Command;

use Rhumsaa\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class RunPendingTidesCommand
{
    /**
     * @JMS\Type("Rhumsaa\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $branch;

    /**
     * @param Uuid   $flowUuid
     * @param string $branch
     */
    public function __construct(Uuid $flowUuid, $branch)
    {
        $this->flowUuid = $flowUuid;
        $this->branch = $branch;
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid()
    {
        return $this->flowUuid;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }
}
