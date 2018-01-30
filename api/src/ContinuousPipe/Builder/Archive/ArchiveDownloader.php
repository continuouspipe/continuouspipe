<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Request\ArchiveSource;

interface ArchiveDownloader
{
    /**
     * @param ArchiveSource $archive
     * @param string        $to      Path of the file to download to.
     *
     * @throws ArchiveException
     */
    public function download(ArchiveSource $archive, string $to);
}
