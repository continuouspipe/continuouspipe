<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface FileSystemResolver
{
    /**
     * Get file system for the given code repository and reference.
     *
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     *
     * @return RelativeFileSystem
     *
     * @throws CodeRepositoryException
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference) : RelativeFileSystem;

    /**
     * Returns true if supports the following flow.
     *
     * @param FlatFlow $flow
     *
     * @return bool
     */
    public function supports(FlatFlow $flow) : bool;
}
