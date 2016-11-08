<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\View\Flow;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(Flow $flow, CodeReference $codeReference);
}
