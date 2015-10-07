<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;
use Docker\Context\Context;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemArchive extends Context implements Archive
{
    /**
     * Delete the archive.
     */
    public function delete()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->getDirectory());
    }
}
