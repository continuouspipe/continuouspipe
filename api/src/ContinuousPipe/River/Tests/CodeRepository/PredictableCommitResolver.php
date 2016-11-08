<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\View\Flow;
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
    public function getLegacyHeadCommitOfBranch(BucketContainer $bucketContainer, CodeRepository $repository, $branch)
    {
        return $this->getCommitByBranch($branch);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(Flow $flow, $branch)
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
}
