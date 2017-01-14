<?php

namespace ContinuousPipe\Builder\Aggregate\Event;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class BuildStepStarted extends BuildEvent
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $stepPosition;

    /**
     * @JMS\Type("ContinuousPipe\Builder\BuildStepConfiguration")
     *
     * @var BuildStepConfiguration
     */
    private $stepConfiguration;

    public function __construct(string $buildIdentifier, int $stepPosition, BuildStepConfiguration $stepConfiguration)
    {
        parent::__construct($buildIdentifier);

        $this->stepPosition = $stepPosition;
        $this->stepConfiguration = $stepConfiguration;
    }

    /**
     * @return int
     */
    public function getStepPosition(): int
    {
        return $this->stepPosition;
    }

    /**
     * @return BuildStepConfiguration
     */
    public function getStepConfiguration(): BuildStepConfiguration
    {
        return $this->stepConfiguration;
    }
}
