<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Request\ArchiveSource as ArchiveRequest;
use ContinuousPipe\Builder\Context;

interface ArchivePacker
{
    /**
     * @param Context        $context
     * @param ArchiveRequest $archive
     *
     * @return Archive
     */
    public function createFromArchiveRequest(Context $context, ArchiveRequest $archive);
}
