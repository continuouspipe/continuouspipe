<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use GitHub\WebHook\Model\PullRequest;
use GitHub\WebHook\Model\Repository;

interface PullRequestDeploymentNotifier
{
    /**
     * Notifies that the deployment is successful for this pull-request.
     *
     * @param DeploymentSuccessful $deploymentSuccessful
     * @param Repository $repository
     * @param PullRequest $pullRequest
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest);
}
