<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\TideRepository;

class DeploymentSuccessfulListener
{
    /**
     * @var PullRequestResolver
     */
    private $pullRequestResolver;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var PullRequestDeploymentNotifier
     */
    private $pullRequestDeploymentNotifier;

    /**
     * @param PullRequestResolver $pullRequestResolver
     * @param TideRepository $tideRepository
     * @param PullRequestDeploymentNotifier $pullRequestDeploymentNotifier
     */
    public function __construct(PullRequestResolver $pullRequestResolver, TideRepository $tideRepository, PullRequestDeploymentNotifier $pullRequestDeploymentNotifier)
    {
        $this->pullRequestResolver = $pullRequestResolver;
        $this->tideRepository = $tideRepository;
        $this->pullRequestDeploymentNotifier = $pullRequestDeploymentNotifier;
    }

    /**
     * @param DeploymentSuccessful $deploymentSuccessful
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful)
    {
        $tide = $this->tideRepository->find($deploymentSuccessful->getTideUuid());
        $pullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference($tide->getUser(), $tide->getCodeReference());

        foreach ($pullRequests as $pullRequest) {
            $codeRepository = $tide->getCodeReference()->getRepository();

            if ($codeRepository instanceof GitHubCodeRepository) {
                $this->pullRequestDeploymentNotifier->notify($deploymentSuccessful, $codeRepository->getGitHubRepository(), $pullRequest);
            }
        }
    }
}
