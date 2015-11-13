<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\Event\GitHub\CommentedTideFeedback;
use ContinuousPipe\River\EventBus\EventStore;
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
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param GitHubClientFactory $gitHubClientFactory
     * @param EventStore $eventStore
     */
    public function __construct(GitHubClientFactory $gitHubClientFactory, EventStore $eventStore)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest)
    {
        $deployment = $deploymentSuccessful->getDeployment();
        $contents = $this->getCommentContents($deployment);

        $client = $this->gitHubClientFactory->createClientForUser($deployment->getUser());
        $comment = $client->issues()->comments()->create(
            $repository->getOwner()->getLogin(),
            $repository->getName(),
            $pullRequest->getNumber(),
            [
                'body' => $contents,
            ]
        );

        $this->eventStore->add(new CommentedTideFeedback($deploymentSuccessful->getTideUuid(), $comment['id']));
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

        $contents = 'The environment has been successfully deployed, here is the list of public endpoints:'.PHP_EOL;
        foreach ($publicEndpoints as $endpoint) {
            $contents .= '- **'.$endpoint->getName().'**: http://'.$endpoint->getAddress().PHP_EOL;
        }

        return $contents;
    }
}
