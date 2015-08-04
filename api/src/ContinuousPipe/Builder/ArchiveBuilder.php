<?php

namespace ContinuousPipe\Builder;

use LogStream\Logger;

interface ArchiveBuilder
{
    /**
     * @param Repository $repository
     * @param Logger     $logger
     *
     * @return Archive
     */
    public function getArchive(Repository $repository, Logger $logger);
}
