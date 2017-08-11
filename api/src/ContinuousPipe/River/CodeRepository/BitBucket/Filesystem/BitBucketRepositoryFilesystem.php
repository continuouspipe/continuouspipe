<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Filesystem;

use ContinuousPipe\River\CodeRepository\FileSystem\FileException;
use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClient;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use GuzzleHttp\Exception\RequestException;

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
                $repository,
                $this->codeReference->getCommitSha() ?: $this->codeReference->getBranch(),
                $filePath
            );
        } catch (BitBucketClientException $e) {
            if ($e->getCode() == 404) {
                throw new FileNotFound($e->getMessage(), $e->getCode(), $e);
            }

            throw new FileException(
                sprintf('Unable to read file "%s". Response from BitBucket: %s', $filePath, $this->formatException($e)),
                $e->getCode(),
                $e
            );
        }
    }

    private function formatException(BitBucketClientException $e)
    {
        $previous = $e->getPrevious();
        if (!$previous instanceof RequestException) {
            return $e->getMessage();
        }

        if (null !== ($response = $previous->getResponse())) {
            return $response->getStatusCode() . ' ' . $response->getReasonPhrase();
        }

        return $e->getMessage();
    }
}
