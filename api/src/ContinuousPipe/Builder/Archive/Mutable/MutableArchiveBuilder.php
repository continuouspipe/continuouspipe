<?php

namespace ContinuousPipe\Builder\Archive\Mutable;

use ContinuousPipe\Builder\Archive;

class MutableArchiveBuilder
{
    /**
     * Create a mutable archive from the given archive.
     *
     * @param Archive $archive
     *
     * @return MutableArchive
     */
    public function createFromArchive(Archive $archive)
    {
        return new MutableArchive();
    }
}
