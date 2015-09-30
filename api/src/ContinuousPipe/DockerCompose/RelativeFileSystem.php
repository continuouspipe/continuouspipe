<?php

namespace ContinuousPipe\DockerCompose;

interface RelativeFileSystem
{
    /**
     * Returns `true` if the file exists.
     *
     * @param string $filePath
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
     *
     * @return string
     */
    public function getContents($filePath);
}
