<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\View\Tide;

class DelegatesToPullRequestResolver implements PullRequestResolver
{
    /**
     * @var array|PullRequestResolver[]
     */
    private $resolvers;

    /**
     * @param PullRequestResolver[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(Tide $tide): array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($tide)) {
                return $resolver->findPullRequestWithHeadReference($tide);
            }
        }

        throw new CodeRepositoryException('No resolver supports the given tide');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($tide)) {
                return true;
            }
        }

        return false;
    }
}
