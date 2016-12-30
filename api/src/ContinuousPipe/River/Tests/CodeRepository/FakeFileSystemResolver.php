<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class FakeFileSystemResolver implements FileSystemResolver
{
    /**
     * @var FileSystemResolver
     */
    private $decoratedFilesystemResolver;

    /**
     * @var array
     */
    private $files = [];

    /**
     * @param FileSystemResolver|null $decoratedFilesystemResolver
     */
    public function __construct(FileSystemResolver $decoratedFilesystemResolver = null)
    {
        $this->decoratedFilesystemResolver = $decoratedFilesystemResolver;
    }

    /**
     * @param array $files
     */
    public function prepareFileSystem(array $files)
    {
        $this->files = array_merge($this->files, $files);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference) : RelativeFileSystem
    {
        return new PredictiveFileSystem(
            $this->files,
            null !== $this->decoratedFilesystemResolver ? $this->decoratedFilesystemResolver->getFileSystem($flow, $codeReference) : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return true;
    }
}
