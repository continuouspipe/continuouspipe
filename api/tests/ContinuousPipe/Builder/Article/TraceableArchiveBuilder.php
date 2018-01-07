<?php

namespace ContinuousPipe\Builder\Article;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class TraceableArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var ArchiveBuilder
     */
    private $decoratedBuilder;

    /**
     * @var BuildStepConfiguration[]
     */
    private $steps;

    /**
     * @var Archive[]
     */
    private $archives;

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

        $this->steps[] = $buildStepConfiguration;
        $this->archives[] = $archive;

        return $archive;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool
    {
        return $this->decoratedBuilder->supports($buildStepConfiguration);
    }

    /**
     * @return BuildStepConfiguration[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @return Archive[]
     */
    public function getArchives()
    {
        return $this->archives;
    }
}
