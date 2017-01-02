<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\CodeRepository\PullRequestCommentManipulator;
use ContinuousPipe\River\View\Tide;

class BitBucketPullRequestCommentManipulator implements PullRequestCommentManipulator
{
    /**
     * @var BitBucketClientFactory
     */
    private $clientFactory;

    public function __construct(BitBucketClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function writeComment(Tide $tide, PullRequest $pullRequest, string $contents): string
    {
        list($repository, $client) = $this->getClientAndRepository($tide);

        try {
            return $client->writePullRequestComment(
                $repository->getOwner()->getUsername(),
                $repository->getName(),
                $pullRequest->getIdentifier(),
                $contents
            );
        } catch (BitBucketClientException $e) {
            throw new CodeRepositoryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteComment(Tide $tide, PullRequest $pullRequest, string $identifier)
    {
        list($repository, $client) = $this->getClientAndRepository($tide);

        try {
            return $client->deletePullRequestComment(
                $repository->getOwner()->getUsername(),
                $repository->getName(),
                $pullRequest->getIdentifier(),
                $identifier
            );
        } catch (BitBucketClientException $e) {
            throw new CodeRepositoryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function supports(Tide $tide): bool
    {
        return $tide->getCodeReference()->getRepository() instanceof BitBucketCodeRepository;
    }

    /**
     * @param Tide $tide
     *
     * @throws CodeRepositoryException
     *
     * @return array
     */
    private function getClientAndRepository(Tide $tide): array
    {
        $repository = $tide->getCodeReference()->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new CodeRepositoryException('This pull-request comment manipulator only supports BitBucket repositories');
        }

        $client = $this->clientFactory->createForCodeRepository($repository);

        return [$repository, $client];
    }
}
