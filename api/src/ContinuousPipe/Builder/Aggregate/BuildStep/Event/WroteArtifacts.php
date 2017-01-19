<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

use ContinuousPipe\Builder\Archive;

class WroteArtifacts extends StepEvent
{
    /**
     * @var Archive[]
     */
    private $archives;

    /**
     * @param string    $buildIdentifier
     * @param int       $stepPosition
     * @param Archive[] $archives
     */
    public function __construct(string $buildIdentifier, int $stepPosition, array $archives)
    {
        parent::__construct($buildIdentifier, $stepPosition);

        $this->archives = $archives;
    }

    /**
     * @return Archive[]
     */
    public function getArchives(): array
    {
        return $this->archives;
    }
}
