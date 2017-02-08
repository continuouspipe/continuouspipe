<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest as BitBucketPullRequest;
use Ramsey\Uuid\UuidInterface;

class BitBucketPullRequestResolver implements PullRequestResolver
{
    /**
     * @var BitBucketClientFactory
     */
    private $bitBucketClientFactory;

    /**
     * @param BitBucketClientFactory $bitBucketClientFactory
     */
    public function __construct(BitBucketClientFactory $bitBucketClientFactory)
    {
        $this->bitBucketClientFactory = $bitBucketClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findPullRequestWithHeadReference(UuidInterface $flowUuid, CodeReference $codeReference): array
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new CodeRepositoryException('This pull-request comment manipulator only supports BitBucket repositories');
        }

        $pullRequests = $this->bitBucketClientFactory->createForCodeRepository($repository)->getOpenedPullRequests(
            $repository->getOwner()->getUsername(),
            $repository->getName()
        );

        $matchingPullRequests = array_values(array_filter($pullRequests, function (BitBucketPullRequest $pullRequest) use ($codeReference) {
            return $codeReference->getBranch() == $pullRequest->getSource()->getBranch()->getName()
                || strpos($codeReference->getCommitSha(), $pullRequest->getSource()->getCommit()->getHash()) === 0;
        }));

        return array_map(function (BitBucketPullRequest $pullRequest) {
            return new PullRequest($pullRequest->getId());
        }, $matchingPullRequests);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof BitBucketCodeRepository;
    }
}
