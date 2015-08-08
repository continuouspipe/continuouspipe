<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\User\User;

interface FileSystemResolver
{
    /**
     * Get file system for the given code repository and reference.
     *
     * @param CodeReference $codeReference
     * @param User          $user
     *
     * @return RelativeFileSystem
     *
     * @throws InvalidRepositoryAddress
     */
    public function getFileSystem(CodeReference $codeReference, User $user);
}
