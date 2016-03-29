<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param CodeReference   $codeReference
     * @param BucketContainer $bucketContainer
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(CodeReference $codeReference, BucketContainer $bucketContainer);
}
