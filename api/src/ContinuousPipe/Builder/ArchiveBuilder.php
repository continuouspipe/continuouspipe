<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\User\User;
use LogStream\Logger;

interface ArchiveBuilder
{
    /**
     * @param Repository $repository
     * @param User       $user
     * @param Logger     $logger
     *
     * @return Archive
     */
    public function getArchive(Repository $repository, User $user, Logger $logger);
}
