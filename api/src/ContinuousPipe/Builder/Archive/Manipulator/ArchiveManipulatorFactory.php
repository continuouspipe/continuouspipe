<?php

namespace ContinuousPipe\Builder\Archive\Manipulator;

use ContinuousPipe\Builder\Archive;

class ArchiveManipulatorFactory
{
    /**
     * @var Archive\ArchiveReader
     */
    private $archiveReader;

    /**
     * @param Archive\ArchiveReader $archiveReader
     */
    public function __construct(Archive\ArchiveReader $archiveReader)
    {
        $this->archiveReader = $archiveReader;
    }

    /**
     * @param Archive $archive
     *
     * @return ArchiveManipulator
     */
    public function getManipulatorForArchive(Archive $archive)
    {
        return new ArchiveManipulator($this->archiveReader, $archive);
    }
}
