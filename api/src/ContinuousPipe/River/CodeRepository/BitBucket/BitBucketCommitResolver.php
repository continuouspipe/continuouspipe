<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class BitBucketCommitResolver implements CommitResolver
{
    /**
     * @var BitBucketClientFactory
     */
    private $clientFactory;

    public function __construct(BitBucketClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadCommitOfBranch(FlatFlow $flow, $branch)
    {
        /** @var BitBucketCodeRepository $repository */
        $repository = $flow->getRepository();

        try {
            return $this->clientFactory->createForCodeRepository($repository)->getReference(
                $repository->getOwner()->getUsername(),
                $repository->getName(),
                $branch
            );
        } catch (BitBucketClientException $e) {
            throw new CommitResolverException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FlatFlow $flow): bool
    {
        return $flow->getRepository() instanceof BitBucketCodeRepository;
    }
}
