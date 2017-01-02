<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest;

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

    /**
     * @param string $owner
     * @param string $repository
     *
     * @throws BitBucketClientException
     *
     * @return PullRequest[]
     */
    public function getOpenedPullRequests(string $owner, string $repository) : array;

    /**
     * Write a pull-request comment.
     *
     * Returns the identifier of the comment.
     *
     * @param string $owner
     * @param string $repository
     * @param string $pullRequestIdentifier
     * @param string $contents
     *
     * @throws BitBucketClientException
     *
     * @return string
     */
    public function writePullRequestComment(string $owner, string $repository, string $pullRequestIdentifier, string $contents) : string;

    /**
     * Delete the given comment.
     *
     * @param string $owner
     * @param string $repository
     * @param string $pullRequestIdentifier
     * @param string $commentIdentifier
     *
     * @throws BitBucketClientException
     */
    public function deletePullRequestComment(string $owner, string $repository, string $pullRequestIdentifier, string $commentIdentifier);
}
