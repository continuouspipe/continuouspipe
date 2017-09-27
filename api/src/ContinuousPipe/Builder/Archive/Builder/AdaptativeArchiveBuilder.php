<?php

namespace ContinuousPipe\Builder\Archive\Builder;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

class AdaptativeArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var array|ArchiveBuilder[]
     */
    private $builders;

    /**
     * @param ArchiveBuilder[] $builders
     */
    public function __construct(array $builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function createArchive(BuildStepConfiguration $buildStepConfiguration) : Archive
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($buildStepConfiguration)) {
                return $builder->createArchive($buildStepConfiguration);
            }
        }

        throw new ArchiveCreationException('No archive builder support such archives');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($buildStepConfiguration)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ArchiveBuilder $builder
     */
    public function addBuilder(ArchiveBuilder $builder)
    {
        $this->builders[] = $builder;
    }
}
