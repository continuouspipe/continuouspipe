<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
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
     * @deprecated The commit resolver is related to a flow. The `getHeadCommitOfBranch` method
     *             should be used
     *
     * @return string
     */
    public function getLegacyHeadCommitOfBranch(BucketContainer $bucketContainer, CodeRepository $repository, $branch);

    /**
     * @param FlatFlow $flow
     * @param string   $branch
     *
     * @throws CommitResolverException
     *
     * @return string
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch);
}
