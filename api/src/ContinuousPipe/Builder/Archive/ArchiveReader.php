<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;

interface ArchiveReader
{
    /**
     * Get the contents of the file at the given path in the archive.
     *
     * @param Archive $archive
     * @param string  $path
     *
     * @return string
     *
     * @throws ArchiveException
     */
    public function getFileContents(Archive $archive, $path);

    /**
     * Extract an archive to the given repository.
     *
     * Returns the path of the directory where the archive was extracted.
     *
     * @param Archive $archive
     * @param string  $path
     *
     * @return string
     *
     * @throws ArchiveException
     */
    public function extract(Archive $archive, $path = null);
}
