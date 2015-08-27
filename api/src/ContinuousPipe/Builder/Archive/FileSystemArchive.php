<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;

class FileSystemArchive implements Archive
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function isStreamed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return fopen($this->filePath, 'r');
    }
}
