<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\GitHub\GitHubClientFactory;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use GitHub\WebHook\Model\PullRequest;
use GitHub\WebHook\Model\Repository;

class CommentPullRequestDeploymentNotifier implements PullRequestDeploymentNotifier
{
    /**
     * @var GitHubClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest)
    {
        $deployment = $deploymentSuccessful->getDeployment();
        $contents = $this->getCommentContents($deployment);

        $client = $this->gitHubClientFactory->createClientForUser($deployment->getUser());
        $client->issues()->comments()->create(
            $repository->getOwner()->getLogin(),
            $repository->getName(),
            $pullRequest->getNumber(),
            [
                'body' => $contents,
            ]
        );
    }

    /**
     * @param Deployment $deployment
     *
     * @return string
     */
    private function getCommentContents(Deployment $deployment)
    {
        $publicEndpoints = $deployment->getPublicEndpoints();
        if (empty($publicEndpoints)) {
            return 'Environment successfully deployed but found no public endpoint.';
        }

        $contents = 'The environment as been successfully deployed, here is the list of public endpoints:'.PHP_EOL;
        foreach ($publicEndpoints as $endpoint) {
            $contents .= $endpoint->getName().': '.$endpoint->getAddress().PHP_EOL;
        }

        return $contents;
    }
}
