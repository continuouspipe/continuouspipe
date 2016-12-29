<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

interface BitBucketClient
{
    /**
     * @param string $owner
     * @param string $repository
     * @param string $branch
     *
     * @throws BitBucketClientException
     *
     * @return string
     */
    public function getReference(string $owner, string $repository, string $branch) : string;

    /**
     * @param string $owner
     * @param string $repository
     * @param string $reference
     * @param string $filePath
     *
     * @throws BitBucketClientException
     *
     * @return string
     */
    public function getContents(string $owner, string $repository, string $reference, string $filePath) : string;

    /**
     * @param string      $owner
     * @param string      $repository
     * @param string      $reference
     * @param BuildStatus $status
     *
     * @throws BitBucketClientException
     */
    public function buildStatus(string $owner, string $repository, string $reference, BuildStatus $status);
}
