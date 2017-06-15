<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
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
            if ($resolver->supports($flowUuid, $codeReference->getRepository())) {
                return $resolver->findPullRequestWithHeadReference($flowUuid, $codeReference);
            }
        }

        throw new CodeRepositoryException('No resolver supports the given tide');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeRepository $repository): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($flowUuid, $repository)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PullRequest[]
     */
    public function findAll(UuidInterface $flowUuid, CodeRepository $repository): array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($flowUuid, $repository)) {
                return $resolver->findAll($flowUuid, $repository);
            }
        }

        throw new CodeRepositoryException('No resolver supports the given tide');
    }
}
