<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Filesystem;

use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
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
            if ($e->getCode() == 404) {
                return false;
            }

            throw new FileException($e->getMessage(), $e->getCode(), $e);
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
                $repository,
                $this->codeReference->getCommitSha() ?: $this->codeReference->getBranch(),
                $filePath
            );
        } catch (BitBucketClientException $e) {
            if ($e->getCode() == 404) {
                throw new FileNotFound($e->getMessage(), $e->getCode(), $e);
            }

            throw new FileException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
