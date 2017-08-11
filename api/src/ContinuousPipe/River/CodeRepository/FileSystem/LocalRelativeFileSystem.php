<?php

namespace ContinuousPipe\River\CodeRepository\FileSystem;

use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;

class LocalRelativeFileSystem implements RelativeFileSystem
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @param string $directory
     */
    public function __construct($directory = null)
    {
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        return file_exists($this->getRealPath($filePath));
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        $realPath = $this->getRealPath($filePath);
        if (!file_exists($realPath)) {
            throw new FileNotFound(sprintf('File "%s" not found', $filePath));
        }

        return file_get_contents($realPath);
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    private function getRealPath($filePath)
    {
        if (!empty($this->directory)) {
            $filePath = $this->directory.DIRECTORY_SEPARATOR.$filePath;
        }

        return $filePath;
    }
}
