<?php

namespace ContinuousPipe\Builder\Aggregate\BuildStep\Event;

use ContinuousPipe\Builder\Archive;

class CodeArchiveCreated extends StepEvent
{
    /**
     * @var Archive
     */
    private $archive;

    public function __construct(string $buildIdentifier, int $stepPosition, Archive $archive)
    {
        parent::__construct($buildIdentifier, $stepPosition);

        $this->archive = $archive;
    }

    /**
     * @return Archive
     */
    public function getArchive(): Archive
    {
        return $this->archive;
    }
}
