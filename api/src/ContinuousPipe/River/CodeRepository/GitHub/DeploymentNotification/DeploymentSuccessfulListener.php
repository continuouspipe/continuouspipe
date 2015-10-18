<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\User\UserRepository;

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
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param PullRequestResolver           $pullRequestResolver
     * @param TideRepository                $tideRepository
     * @param PullRequestDeploymentNotifier $pullRequestDeploymentNotifier
     * @param UserRepository                $userRepository
     */
    public function __construct(PullRequestResolver $pullRequestResolver, TideRepository $tideRepository, PullRequestDeploymentNotifier $pullRequestDeploymentNotifier, UserRepository $userRepository)
    {
        $this->pullRequestResolver = $pullRequestResolver;
        $this->tideRepository = $tideRepository;
        $this->pullRequestDeploymentNotifier = $pullRequestDeploymentNotifier;
        $this->userRepository = $userRepository;
    }

    /**
     * @param DeploymentSuccessful $deploymentSuccessful
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful)
    {
        $tide = $this->tideRepository->find($deploymentSuccessful->getTideUuid());
        $user = $this->userRepository->findOneByUsername($tide->getUser()->getUsername());

        $pullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference($user, $tide->getCodeReference());

        foreach ($pullRequests as $pullRequest) {
            $codeRepository = $tide->getCodeReference()->getRepository();

            if ($codeRepository instanceof GitHubCodeRepository) {
                $this->pullRequestDeploymentNotifier->notify($deploymentSuccessful, $codeRepository->getGitHubRepository(), $pullRequest);
            }
        }
    }
}
