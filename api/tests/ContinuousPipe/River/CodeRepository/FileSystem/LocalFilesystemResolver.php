<?php

namespace ContinuousPipe\River\CodeRepository\FileSystem;

use ContinuousPipe\River\CodeRepository\FileSystem\LocalRelativeFileSystem;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class LocalFilesystemResolver implements FileSystemResolver
{
    private $overwriteFileSystemWithLocalPath = null;

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference): RelativeFileSystem
    {
        return new LocalRelativeFileSystem($this->overwriteFileSystemWithLocalPath);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return null !== $this->overwriteFileSystemWithLocalPath;
    }

    public function overwriteFileSystemWithLocalPath(string $overwriteFileSystemWithLocalPath)
    {
        $this->overwriteFileSystemWithLocalPath = $overwriteFileSystemWithLocalPath;
    }
}
