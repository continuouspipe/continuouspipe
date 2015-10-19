<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param CodeReference $codeReference
     * @param Bucket $credentialsBucket
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(CodeReference $codeReference, Bucket $credentialsBucket);
}
