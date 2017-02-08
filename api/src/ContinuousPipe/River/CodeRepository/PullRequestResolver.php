<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\UuidInterface;

interface PullRequestResolver
{
    /**
     * Get the pull request which have the head commit of the given tide.
     *
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @throws CodeRepositoryException
     *
     * @return PullRequest[]
     */
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference) : array;

    /**
     * Return true if the pull-request resolver supports the tide.
     *
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @return bool
     */
    public function supports(UuidInterface $flowUuid, CodeReference $codeReference) : bool;
}
