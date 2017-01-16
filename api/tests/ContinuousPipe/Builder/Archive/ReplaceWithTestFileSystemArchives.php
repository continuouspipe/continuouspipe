<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Tests\Archive\NonDeletableFileSystemArchive;
use LogStream\Logger;

class ReplaceWithTestFileSystemArchives implements ArchiveBuilder
{
    /**
     * @var ArchiveBuilder
     */
    private $decoratedBuilder;

    /**
     * @param ArchiveBuilder $decoratedBuilder
     */
    public function __construct(ArchiveBuilder $decoratedBuilder)
    {
        $this->decoratedBuilder = $decoratedBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function createArchive(BuildStepConfiguration $buildStepConfiguration) : Archive
    {
        $archive = $this->decoratedBuilder->createArchive($buildStepConfiguration);

        if ($archive instanceof FileSystemArchive) {
            $archive = new NonDeletableFileSystemArchive(
                $archive->getDirectory()
            );
        }

        return $archive;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool
    {
        return $this->decoratedBuilder->supports($buildStepConfiguration);
    }
}
