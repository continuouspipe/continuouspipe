<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\LogStream\Logger;

interface ArchiveBuilder
{
    public function getArchive(Repository $repository, Logger $logger);
}
