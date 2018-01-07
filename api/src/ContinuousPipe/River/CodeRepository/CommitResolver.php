<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface CommitResolver
{
    /**
     * @param FlatFlow $flow
     * @param string   $branch
     *
     * @throws CommitResolverException
     *
     * @return string
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch);

    /**
     * Returns true if the commit resolver supports the given flow.
     *
     * @param FlatFlow $flow
     *
     * @return bool
     */
    public function supports(FlatFlow $flow) : bool;
}
