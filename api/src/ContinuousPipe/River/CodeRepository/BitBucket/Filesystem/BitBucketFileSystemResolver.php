<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Filesystem;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientFactory;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class BitBucketFileSystemResolver implements FileSystemResolver
{
    private $bitBucketClientFactory;

    public function __construct(BitBucketClientFactory $bitBucketClientFactory)
    {
        $this->bitBucketClientFactory = $bitBucketClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSystem(FlatFlow $flow, CodeReference $codeReference): RelativeFileSystem
    {
        try {
            return new BitBucketRepositoryFilesystem(
                $this->bitBucketClientFactory->createForCodeRepository($flow->getRepository()),
                $codeReference
            );
        } catch (BitBucketClientException $e) {
            throw new CodeRepositoryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof BitBucketCodeRepository;
    }
}
