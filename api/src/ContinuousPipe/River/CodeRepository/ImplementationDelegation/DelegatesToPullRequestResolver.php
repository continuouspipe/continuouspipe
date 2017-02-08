<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\View\Tide;
use Ramsey\Uuid\UuidInterface;

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
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference): array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($flowUuid, $codeReference)) {
                return $resolver->findPullRequestWithHeadReference($flowUuid, $codeReference);
            }
        }

        throw new CodeRepositoryException('No resolver supports the given tide');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeReference $codeReference): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($flowUuid, $codeReference)) {
                return true;
            }
        }

        return false;
    }
}
