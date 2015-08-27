<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\User\User;
use LogStream\Logger;

interface ArchiveBuilder
{
    /**
     * @param BuildRequest $buildRequest
     * @param User         $user
     * @param Logger       $logger
     *
     * @return Archive
     */
    public function getArchive(BuildRequest $buildRequest, User $user, Logger $logger);
}
