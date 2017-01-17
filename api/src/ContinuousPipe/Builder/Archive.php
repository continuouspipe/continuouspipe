<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Builder\Archive\ArchiveException;
use Docker\Context\ContextInterface;

interface Archive extends ContextInterface
{
    /**
     * Delete the archive.
     */
    public function delete();

    /**
     * Write the archive content at the given path.
     *
     * @param string $path
     * @param Archive $archive
     *
     * @throws ArchiveException
     */
    public function write(string $path, Archive $archive);
}
