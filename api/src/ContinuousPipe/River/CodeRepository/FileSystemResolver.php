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
     * @param Flow          $flow
     * @param CodeReference $codeReference
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
     * @deprecated Being dependent on a Bucket container, this method is deprecated. Please
     *             use the `getFileSystem` method.
     *
     * @return RelativeFileSystem
     *
     * @throws InvalidRepositoryAddress
     */
    public function getFileSystemWithBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer);
}
