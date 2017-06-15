<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\AtlassianAddon\BitBucket\PullRequest as BitBucketPullRequest;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
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
        $pullRequests = $this->fetchAll($codeReference->getRepository());

        $matchingPullRequests = array_values(
            array_filter(
                $pullRequests,
                function (BitBucketPullRequest $pullRequest) use ($codeReference) {
                    return $codeReference->getBranch() == $pullRequest->getSource()->getBranch()->getName()
                    || strpos($codeReference->getCommitSha(), $pullRequest->getSource()->getCommit()->getHash()) === 0;
                }
            )
        );

        return $this->toPullRequests($matchingPullRequests);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UuidInterface $flowUuid, CodeRepository $repository): bool
    {
        return $repository instanceof BitBucketCodeRepository;
    }

    /**
     * @return PullRequest[]
     */
    public function findAll(UuidInterface $flowUuid, CodeRepository $repository): array
    {
        return $this->toPullRequests($this->fetchAll($repository));
    }

    private function toPullRequests($matchingPullRequests)
    {
        return array_map(
            function (BitBucketPullRequest $pullRequest) {
                return new PullRequest(
                    $pullRequest->getId(),
                    $pullRequest->getTitle(),
                    new Branch($pullRequest->getSource()->getBranch()->getName())
                );
            },
            $matchingPullRequests
        );
    }

    private function fetchAll(CodeRepository $repository)
    {
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new CodeRepositoryException(
                'This pull-request resolver only supports BitBucket repositories'
            );
        }

        return $this->bitBucketClientFactory->createForCodeRepository($repository)->getOpenedPullRequests($repository);
    }
}
