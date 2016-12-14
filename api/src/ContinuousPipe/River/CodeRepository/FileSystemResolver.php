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
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference);

    /**
     * Get file system for the given code repository and reference.
     *
     * @param CodeReference   $codeReference
     * @param BucketContainer $bucketContainer
     *
     * @deprecated Being dependent on a Bucket container, this method is deprecated. Please
     *             use the `getFileSystem` method
     *
     * @return RelativeFileSystem
     *
     * @throws CodeRepositoryException
     */
    public function getFileSystemWithBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer);
}
