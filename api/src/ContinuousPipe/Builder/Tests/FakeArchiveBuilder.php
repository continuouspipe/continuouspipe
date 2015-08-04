<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Repository;
use LogStream\Logger;

class FakeArchiveBuilder implements ArchiveBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getArchive(Repository $repository, Logger $logger)
    {
        return new FakeArchive('');
    }
}
