<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Credentials\BucketContainer;

interface CommitResolver
{
    /**
     * @param BucketContainer $bucketContainer
     * @param CodeRepository  $repository
     * @param string          $branch
     *
     * @throws CommitResolverException
     *
     * @return string
     */
    public function getHeadCommitOfBranch(BucketContainer $bucketContainer, CodeRepository $repository, $branch);
}
