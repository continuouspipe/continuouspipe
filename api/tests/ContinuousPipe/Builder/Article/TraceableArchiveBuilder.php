<?php

namespace ContinuousPipe\Builder\Article;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class TraceableArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var ArchiveBuilder
     */
    private $decoratedBuilder;

    /**
     * @var BuildRequest[]
     */
    private $requests;

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

        $this->requests[] = $buildRequest;

        return $archive;
    }

    /**
     * @return BuildRequest[]
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
