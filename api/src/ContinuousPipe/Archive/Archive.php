<?php

namespace ContinuousPipe\Archive;

use Psr\Http\Message\StreamInterface;

interface Archive
{
    const TAR = 'tar';
    const TAR_GZ = 'targz';

    /**
     * Read the archive, with a given format.
     *
     * @param string $format
     *
     * @throws ArchiveException
     *
     * @return StreamInterface
     */
    public function read(string $format) : StreamInterface;
}
