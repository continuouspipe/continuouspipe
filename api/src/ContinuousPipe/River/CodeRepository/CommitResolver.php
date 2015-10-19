<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\Security\Credentials\Bucket;

interface CommitResolver
{
    /**
     * @param Bucket         $credentialsBucket
     * @param CodeRepository $repository
     * @param string         $branch
     *
     * @return string
     */
    public function getHeadCommitOfBranch(Bucket $credentialsBucket, CodeRepository $repository, $branch);
}
