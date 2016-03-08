<?php

namespace ContinuousPipe\River\Recover\Notification\PullRequest;

use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\CodeRepository\NotificationException;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use GitHub\WebHook\Model\PullRequest;
use GitHub\WebHook\Model\Repository;
use Psr\Log\LoggerInterface;

class OptionalPullRequestDeploymentNotifier implements PullRequestDeploymentNotifier
{
    /**
     * @var PullRequestDeploymentNotifier
     */
    private $decoratedNotifier;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PullRequestDeploymentNotifier $decoratedNotifier
     * @param LoggerInterface               $logger
     */
    public function __construct(PullRequestDeploymentNotifier $decoratedNotifier, LoggerInterface $logger)
    {
        $this->decoratedNotifier = $decoratedNotifier;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest)
    {
        try {
            $this->decoratedNotifier->notify($deploymentSuccessful, $repository, $pullRequest);
        } catch (NotificationException $e) {
            $this->logger->error('Unable to notify tide success on the pull-request', [
                'exception' => $e,
                'tideUuid' => (string) $deploymentSuccessful->getTideUuid(),
            ]);
        }
    }
}
