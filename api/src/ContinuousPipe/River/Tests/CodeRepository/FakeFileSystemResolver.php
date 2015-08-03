<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;

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
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(CodeRepository $repository, CodeReference $codeReference)
    {
        return new PredictiveFileSystem($this->files);
    }
}
