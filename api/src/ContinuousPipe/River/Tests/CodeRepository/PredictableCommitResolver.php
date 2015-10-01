<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\User\User;

class PredictableCommitResolver implements CommitResolver
{
    /**
     * @var array
     */
    private $resolutions = [];

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(CodeRepository $repository, User $user, $branch)
    {
        if (!array_key_exists($branch, $this->resolutions)) {
            throw new CommitResolverException(sprintf(
                'Unable to find predictable resolution of branch "%s"',
                $branch
            ));
        }

        return $this->resolutions[$branch];
    }

    /**
     * @param string $branch
     * @param string $sha1
     */
    public function headOfBranchIs($branch, $sha1)
    {
        $this->resolutions[$branch] = $sha1;
    }
}
