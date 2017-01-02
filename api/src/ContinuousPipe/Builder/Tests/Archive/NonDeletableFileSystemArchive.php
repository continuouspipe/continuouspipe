<?php

namespace ContinuousPipe\Builder\Tests\Archive;

use ContinuousPipe\Builder\Archive;

class NonDeletableFileSystemArchive extends Archive\FileSystemArchive
{
    public function delete()
    {
    }
}
