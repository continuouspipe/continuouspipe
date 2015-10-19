<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Logger;

interface ArchiveBuilder
{
    /**
     * @param BuildRequest $buildRequest
     * @param Logger       $logger
     *
     * @throws ArchiveCreationException
     *
     * @return Archive
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger);
}
