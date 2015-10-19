<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

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
    public function getFileSystem(CodeReference $codeReference, Team $team)
    {
        return new PredictiveFileSystem($this->files);
    }
}
