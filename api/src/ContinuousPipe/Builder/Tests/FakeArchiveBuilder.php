<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\User\User;
use LogStream\Logger;

class FakeArchiveBuilder implements ArchiveBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getArchive(Repository $repository, User $user, Logger $logger)
    {
        return new FakeArchive('');
    }
}
