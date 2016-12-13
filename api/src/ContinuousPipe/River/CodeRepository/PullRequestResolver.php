<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Ramsey\Uuid\UuidInterface;

interface PullRequestResolver
{
    /**
     * Get the pull request which have this head commit.
     *
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @return \GitHub\WebHook\Model\PullRequest[]
     */
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference);
}
