<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class BitBucketCommitResolver implements CommitResolver
{
    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch)
    {
        throw new \RuntimeException('Unable to get the information about the repository');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof BitBucketCodeRepository;
    }
}
