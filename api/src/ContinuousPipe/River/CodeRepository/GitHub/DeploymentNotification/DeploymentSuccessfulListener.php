<?php

namespace ContinuousPipe\River\CodeRepository\GitHub\DeploymentNotification;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Credentials\BucketRepository;
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
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param PullRequestResolver $pullRequestResolver
     * @param TideRepository $tideRepository
     * @param PullRequestDeploymentNotifier $pullRequestDeploymentNotifier
     * @param BucketRepository $bucketRepository
     */
    public function __construct(PullRequestResolver $pullRequestResolver, TideRepository $tideRepository, PullRequestDeploymentNotifier $pullRequestDeploymentNotifier, BucketRepository $bucketRepository)
    {
        $this->pullRequestResolver = $pullRequestResolver;
        $this->tideRepository = $tideRepository;
        $this->pullRequestDeploymentNotifier = $pullRequestDeploymentNotifier;
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * @param DeploymentSuccessful $deploymentSuccessful
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful)
    {
        $tide = $this->tideRepository->find($deploymentSuccessful->getTideUuid());
        $bucket = $this->bucketRepository->find($tide->getTeam()->getBucketUuid());

        $pullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference($tide->getCodeReference(), $bucket);

        foreach ($pullRequests as $pullRequest) {
            $codeRepository = $tide->getCodeReference()->getRepository();

            if ($codeRepository instanceof GitHubCodeRepository) {
                $this->pullRequestDeploymentNotifier->notify($deploymentSuccessful, $codeRepository->getGitHubRepository(), $pullRequest);
            }
        }
    }
}
