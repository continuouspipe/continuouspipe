<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use GitHub\WebHook\Model\PullRequest;
use GitHub\WebHook\Model\Repository;

class FakePullRequestDeploymentNotifier implements PullRequestDeploymentNotifier
{
    private $notifications = [];

    /**
     * {@inheritdoc}
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest)
    {
        $this->notifications[] = [$deploymentSuccessful, $repository, $pullRequest];
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
