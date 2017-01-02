<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\ArchiveBuilder;
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
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        $archive = $this->decoratedBuilder->getArchive($buildRequest, $logger);

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
    public function supports(BuildRequest $request)
    {
        return $this->decoratedBuilder->supports($request);
    }
}
