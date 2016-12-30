<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

interface PullRequestResolver
{
    /**
     * Get the pull request which have the head commit of the given tide.
     *
     * @param Tide $tide
     *
     * @throws CodeRepositoryException
     *
     * @return PullRequest[]
     */
    public function findPullRequestWithHeadReference(Tide $tide) : array;

    /**
     * Return true if the pull-request resolver supports the tide.
     *
     * @param Tide $tide
     *
     * @return bool
     */
    public function supports(Tide $tide) : bool;
}
