<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;

interface FileSystemResolver
{
    /**
     * Get file system for the given code repository and reference.
     *
     * @param CodeReference $codeReference
     * @param Team                  $team
     *
     * @return RelativeFileSystem
     *
     * @throws InvalidRepositoryAddress
     */
    public function getFileSystem(CodeReference $codeReference, Team $team);
}
