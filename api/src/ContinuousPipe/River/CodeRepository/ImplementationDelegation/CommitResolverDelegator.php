<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class CommitResolverDelegator implements CommitResolver
{
    /**
     * @var CommitResolver[]
     */
    private $commitResolvers;

    /**
     * @param CommitResolver[] $commitResolvers
     */
    public function __construct(array $commitResolvers)
    {
        $this->commitResolvers = $commitResolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch)
    {
        foreach ($this->commitResolvers as $resolver) {
            if ($resolver->supports($flow)) {
                return $resolver->getHeadCommitOfBranch($flow, $branch);
            }
        }

        throw new CommitResolverException('Unable to find a commit resolver that supports the given flow');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        foreach ($this->commitResolvers as $resolver) {
            if ($resolver->supports($flow)) {
                return true;
            }
        }

        return false;
    }
}
