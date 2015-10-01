<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\User\User;

interface CommitResolver
{
    /**
     * @param CodeRepository $repository
     * @param User           $user
     * @param string         $branch
     *
     * @return string
     *
     * @throws CommitResolverException
     */
    public function getHeadCommitOfBranch(CodeRepository $repository, User $user, $branch);
}
