<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Filesystem;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClient;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;

class BitBucketRepositoryFilesystem implements RelativeFileSystem
{
    private $bitBucketClient;
    private $codeReference;

    public function __construct(BitBucketClient $bitBucketClient, CodeReference $codeReference)
    {
        $this->bitBucketClient = $bitBucketClient;
        $this->codeReference = $codeReference;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filePath)
    {
        try {
            $this->getContents($filePath);

            return true;
        } catch (FileNotFound $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filePath)
    {
        try {
            /** @var BitBucketCodeRepository $repository */
            $repository = $this->codeReference->getRepository();

            return $this->bitBucketClient->getContents(
                $repository->getOwner()->getUsername(),
                $repository->getName(),
                $this->codeReference->getCommitSha() ?: $this->codeReference->getBranch(),
                $filePath
            );
        } catch (BitBucketClientException $e) {
            throw new FileNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
