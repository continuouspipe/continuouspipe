<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface FileSystemResolver
{
    /**
     * Get file system for the given code repository and reference.
     *
     * @param Flow $flow
     * @param CodeReference   $codeReference
     *
     * @return RelativeFileSystem
     *
     * @throws InvalidRepositoryAddress
     */
    public function getFileSystem(Flow $flow, CodeReference $codeReference);

    /**
     * Get file system for the given code repository and reference.
     *
     * @param CodeReference   $codeReference
     * @param BucketContainer $bucketContainer
     *
     * @return RelativeFileSystem
     *
     * @throws InvalidRepositoryAddress
     */
    public function getFileSystemWithBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer);
}
