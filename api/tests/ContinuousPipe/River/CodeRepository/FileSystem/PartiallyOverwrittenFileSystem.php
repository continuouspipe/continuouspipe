<?php

namespace ContinuousPipe\River\CodeRepository\FileSystem;

use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;

class PartiallyOverwrittenFileSystem implements RelativeFileSystem
{
    /**
     * @var RelativeFileSystem
     */
    private $decoratedFileSystem;

    /**
     * @var array
     */
    private $overwriteRules;

    /**
     * @param RelativeFileSystem $decoratedFileSystem
     * @param array $overwriteRules
     */
    public function __construct(RelativeFileSystem $decoratedFileSystem, array $overwriteRules)
    {
        $this->decoratedFileSystem = $decoratedFileSystem;
        $this->overwriteRules = $overwriteRules;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        if (array_key_exists($filePath, $this->overwriteRules)) {
            return true;
        }

        return $this->decoratedFileSystem->exists($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        if (array_key_exists($filePath, $this->overwriteRules)) {
            return $this->overwriteRules[$filePath];
        }

        return $this->decoratedFileSystem->getContents($filePath);
    }
}
