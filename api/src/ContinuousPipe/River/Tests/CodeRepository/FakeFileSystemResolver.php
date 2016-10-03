<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\View\Flow;
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
    public function getFileSystem(Flow $flow, CodeReference $codeReference)
    {
        return new PredictiveFileSystem($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystemWithBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer)
    {
        return new PredictiveFileSystem($this->files);
    }
}
