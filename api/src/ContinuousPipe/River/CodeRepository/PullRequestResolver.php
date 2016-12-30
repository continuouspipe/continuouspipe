<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @return PullRequest[]
     */
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference) : array;
}
