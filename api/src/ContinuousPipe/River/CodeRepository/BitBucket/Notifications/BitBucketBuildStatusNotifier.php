<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Notifications;

use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientException;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketClientFactory;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketStateResolver;
use ContinuousPipe\River\CodeRepository\BitBucket\BuildStatus;
use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;

class BitBucketBuildStatusNotifier implements Notifier
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
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        $buildStatus = (new BuildStatus('CP'))
            ->withState(BitBucketStateResolver::fromStatus($status))
            ->withDescription($status->getDescription())
            ->withUrl($status->getUrl())
        ;

        /** @var BitBucketCodeRepository $repository */
        $repository = $tide->getCodeReference()->getRepository();
        $client = $this->bitBucketClientFactory->createForCodeRepository($repository);

        try {
            $client->buildStatus(
                $repository->getOwner()->getUsername(),
                $repository->getName(),
                $tide->getCodeReference()->getCommitSha(),
                $buildStatus
            );
        } catch (BitBucketClientException $e) {
            throw new NotificationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $tide->getCodeReference()->getRepository() instanceof BitBucketCodeRepository;
    }
}
