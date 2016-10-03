<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\CodeRepository\NotificationException;
use ContinuousPipe\River\Event\GitHub\CommentedTideFeedback;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Github\Client;
use GitHub\WebHook\Model\PullRequest;
use GitHub\WebHook\Model\Repository;

class CommentPullRequestDeploymentNotifier implements PullRequestDeploymentNotifier
{
    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param ClientFactory  $gitHubClientFactory
     * @param EventStore     $eventStore
     * @param TideRepository $tideRepository
     */
    public function __construct(ClientFactory $gitHubClientFactory, EventStore $eventStore, TideRepository $tideRepository)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest)
    {
        $deployment = $deploymentSuccessful->getDeployment();
        $contents = $this->getCommentContents($deployment);
        $tide = $this->tideRepository->find($deploymentSuccessful->getTideUuid());

        try {
            $client = $this->gitHubClientFactory->createClientForFlow($tide->getFlow());
        } catch (UserCredentialsNotFound $e) {
            throw new NotificationException('No valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        // Remove previous comments
        $this->removePreviousComments($client, $repository, $tide);

        // Create the new comment
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

    /**
     * @param Client     $client
     * @param Repository $repository
     * @param Tide       $tide
     */
    private function removePreviousComments(Client $client, Repository $repository, Tide $tide)
    {
        $tides = $this->tideRepository->findByBranch($tide->getFlow()->getUuid(), $tide->getCodeReference());
        foreach ($tides as $tide) {
            $commentEvents = $this->eventStore->findByTideUuidAndType($tide->getUuid(), CommentedTideFeedback::class);
            foreach ($commentEvents as $event) {
                /* @var CommentedTideFeedback $event */
                try {
                    $client->issues()->comments()->remove(
                        $repository->getOwner()->getLogin(),
                        $repository->getName(),
                        $event->getCommentId()
                    );
                } catch (\Exception $e) {
                    // Might be handled better but as this is not really mandatory...
                }
            }
        }
    }
}
