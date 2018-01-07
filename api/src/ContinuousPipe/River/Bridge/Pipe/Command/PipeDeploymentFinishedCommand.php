<?php

namespace ContinuousPipe\River\Bridge\Pipe\Command;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class PipeDeploymentFinishedCommand
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $deploymentUuid;

    /**
     * @param UuidInterface $deploymentUuid
     */
    public function __construct(UuidInterface $deploymentUuid)
    {
        $this->deploymentUuid = $deploymentUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getDeploymentUuid(): UuidInterface
    {
        return $this->deploymentUuid;
    }
}
