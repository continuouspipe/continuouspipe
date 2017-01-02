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
            if ($builder->supports($buildRequest)) {
                return $builder->getArchive($buildRequest, $logger);
            }
        }

        throw new ArchiveCreationException('No archive builder support such archives');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildRequest $request)
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($request)) {
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
