<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\User;
use LogStream\Logger;

class FakeArchiveBuilder implements ArchiveBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, User $user, Logger $logger)
    {
        return new FakeArchive('');
    }
}
