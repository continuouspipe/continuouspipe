<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class BuildStepFinished extends BuildEvent
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $stepPosition;

    public function __construct(string $buildIdentifier, int $stepPosition)
    {
        parent::__construct($buildIdentifier);

        $this->stepPosition = $stepPosition;
    }

    /**
     * @return int
     */
    public function getStepPosition(): int
    {
        return $this->stepPosition;
    }
}
