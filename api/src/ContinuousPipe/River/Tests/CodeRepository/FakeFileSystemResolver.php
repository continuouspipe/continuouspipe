<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\User\User;

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
    public function getFileSystem(CodeReference $codeReference, User $user)
    {
        return new PredictiveFileSystem($this->files);
    }
}
