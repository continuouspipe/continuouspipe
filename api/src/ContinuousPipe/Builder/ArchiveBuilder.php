<?php

namespace ContinuousPipe\Builder;

use LogStream\Logger;

interface ArchiveBuilder
{
    public function getArchive(Repository $repository, Logger $logger);
}
