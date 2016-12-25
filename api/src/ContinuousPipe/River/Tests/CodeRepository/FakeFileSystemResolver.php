<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\BucketContainer;

class FakeFileSystemResolver implements FileSystemResolver
{
    /**
     * @var array
     */
    private $files = [];

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
        return new PredictiveFileSystem($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof GitHubCodeRepository;
    }
}
