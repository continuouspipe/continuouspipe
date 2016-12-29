<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class PredictableCommitResolver implements CommitResolver
{
    /**
     * @var array
     */
    private $resolutions = [];

    /**
     * @var CommitResolver
     */
    private $decoratedResolver;

    public function __construct(CommitResolver $decoratedResolver)
    {
        $this->decoratedResolver = $decoratedResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch)
    {
        if (!array_key_exists($branch, $this->resolutions)) {
            return $this->decoratedResolver->getHeadCommitOfBranch($flow, $branch);
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

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return true;
    }
}
