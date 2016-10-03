<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param Flow $flow
     * @param CodeReference   $codeReference
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(Flow $flow, CodeReference $codeReference);

    /**
     * Get the pull request which have this head commit.
     *
     * @param CodeReference   $codeReference
     * @param BucketContainer $bucketContainer
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReferenceAndBucketContainer(CodeReference $codeReference, BucketContainer $bucketContainer);
}
