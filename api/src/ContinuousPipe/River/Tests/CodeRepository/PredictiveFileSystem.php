<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;

class PredictiveFileSystem implements RelativeFileSystem
{
    /**
     * @var array
     */
    private $files;

    /**
     * @param array $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        return array_key_exists($filePath, $this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        if (!$this->exists($filePath)) {
            throw new FileNotFound();
        }

        return $this->files[$filePath];
    }
}
