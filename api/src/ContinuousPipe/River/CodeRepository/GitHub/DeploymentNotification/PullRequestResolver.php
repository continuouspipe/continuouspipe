<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\User\User;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param User $user
     * @param CodeReference $codeReference
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(User $user, CodeReference $codeReference);
}
