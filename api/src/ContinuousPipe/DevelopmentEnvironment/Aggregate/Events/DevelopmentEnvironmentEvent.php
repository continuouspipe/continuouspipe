<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate\Events;

use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

abstract class DevelopmentEnvironmentEvent
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $developmentEnvironmentUuid;

    /**
     * @param UuidInterface $developmentEnvironmentUuid
     */
    public function __construct(UuidInterface $developmentEnvironmentUuid)
    {
        $this->developmentEnvironmentUuid = $developmentEnvironmentUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getDevelopmentEnvironmentUuid(): UuidInterface
    {
        return $this->developmentEnvironmentUuid;
    }
}
