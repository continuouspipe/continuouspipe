<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest as BitBucketPullRequest;

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
    public function findPullRequestWithHeadReference(Tide $tide): array
    {
        $repository = $tide->getCodeReference()->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new CodeRepositoryException('This pull-request comment manipulator only supports BitBucket repositories');
        }

        $pullRequests = $this->bitBucketClientFactory->createForCodeRepository($repository)->getOpenedPullRequests(
            $repository->getOwner()->getUsername(),
            $repository->getName()
        );

        $codeReference = $tide->getCodeReference();
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
    public function supports(Tide $tide): bool
    {
        return $tide->getCodeReference()->getRepository() instanceof BitBucketCodeRepository;
    }
}
