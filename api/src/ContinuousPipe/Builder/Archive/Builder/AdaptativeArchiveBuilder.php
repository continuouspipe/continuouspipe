<?php

namespace ContinuousPipe\Builder\Archive\Builder;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
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
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        foreach ($this->builders as $builder) {
            if ($builder instanceof ConditionalArchiveBuilder && !$builder->supports($buildRequest)) {
                continue;
            }

            return $builder->getArchive($buildRequest, $logger);
        }

        throw new ArchiveCreationException('No archive builder support such archives');
    }

    /**
     * @param ArchiveBuilder $builder
     */
    public function addBuilder(ArchiveBuilder $builder)
    {
        $this->builders[] = $builder;
    }
}
