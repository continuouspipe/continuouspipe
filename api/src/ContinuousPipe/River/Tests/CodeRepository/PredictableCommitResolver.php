<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\BucketContainer;

class PredictableCommitResolver implements CommitResolver
{
    /**
     * @var array
     */
    private $resolutions = [];

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch)
    {
        return $this->getCommitByBranch($branch);
    }

    /**
     * @param string $branch
     * @param string $sha1
     */
    public function headOfBranchIs($branch, $sha1)
    {
        $this->resolutions[$branch] = $sha1;
    }

    /**
     * @param $branch
     *
     * @return mixed
     *
     * @throws CommitResolverException
     */
    private function getCommitByBranch($branch)
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
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof CodeRepository\GitHub\GitHubCodeRepository;
    }
}
