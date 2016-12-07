<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param \ContinuousPipe\River\Flow\Projections\FlatFlow          $flow
     * @param CodeReference $codeReference
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(FlatFlow $flow, CodeReference $codeReference);
}
