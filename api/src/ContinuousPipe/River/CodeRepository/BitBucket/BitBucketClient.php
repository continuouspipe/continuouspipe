<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest;

interface BitBucketClient
{
    /**
     * @param BitBucketCodeRepository $codeRepository
     * @param string $branch
     *
     * @throws BitBucketClientException
     *
     * @return string
     */
    public function getReference(BitBucketCodeRepository $codeRepository, string $branch) : string;

    /**
     * @param BitBucketCodeRepository $codeRepository
     * @param string $reference
     * @param string $filePath
     *
     * @throws BitBucketClientException
     *
     * @return string
     */
    public function getContents(BitBucketCodeRepository $codeRepository, string $reference, string $filePath) : string;

    /**
     * @param BitBucketCodeRepository $codeRepository
     * @param string      $reference
     * @param BuildStatus $status
     *
     * @throws BitBucketClientException
     */
    public function buildStatus(BitBucketCodeRepository $codeRepository, string $reference, BuildStatus $status);

    /**
     * @param BitBucketCodeRepository $codeRepository
     *
     * @throws BitBucketClientException
     *
     * @return PullRequest[]
     */
    public function getOpenedPullRequests(BitBucketCodeRepository $codeRepository) : array;

    /**
     * Write a pull-request comment.
     *
     * Returns the identifier of the comment.
     *
     * @param BitBucketCodeRepository $codeRepository
     * @param string $pullRequestIdentifier
     * @param string $contents
     *
     * @throws BitBucketClientException
     *
     * @return string
     */
    public function writePullRequestComment(BitBucketCodeRepository $codeRepository, string $pullRequestIdentifier, string $contents) : string;

    /**
     * Delete the given comment.
     *
     * @param BitBucketCodeRepository $codeRepository
     * @param string $pullRequestIdentifier
     * @param string $commentIdentifier
     *
     * @throws BitBucketClientException
     */
    public function deletePullRequestComment(BitBucketCodeRepository $codeRepository, string $pullRequestIdentifier, string $commentIdentifier);
}
