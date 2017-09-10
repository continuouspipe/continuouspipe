<?php

namespace ContinuousPipe\River\CodeRepository\FileSystem;

use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;

interface RelativeFileSystem
{
    /**
     * Returns `true` if the file exists.
     *
     * @param string $filePath
     *
     * @throws FileException
     *
     * @return bool
     */
    public function exists($filePath);

    /**
     * Get file contents.
     *
     * @param string $filePath
     *
     * @throws FileNotFound
     * @throws FileException
     *
     * @return string
     */
    public function getContents($filePath);
}
