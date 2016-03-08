<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\Pipe\Client\Deployment;
use ContinuousPipe\River\CodeRepository\NotificationException;
use ContinuousPipe\River\Event\GitHub\CommentedTideFeedback;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
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
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param ClientFactory    $gitHubClientFactory
     * @param EventStore       $eventStore
     * @param TideRepository   $tideRepository
     * @param BucketRepository $bucketRepository
     */
    public function __construct(ClientFactory $gitHubClientFactory, EventStore $eventStore, TideRepository $tideRepository, BucketRepository $bucketRepository)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
        $this->bucketRepository = $bucketRepository;
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
            $bucket = $this->bucketRepository->find($tide->getTeam()->getBucketUuid());
        } catch (BucketNotFound $e) {
            throw new NotificationException('The credentials bucket do not exists', $e->getCode(), $e);
        }

        try {
            $client = $this->gitHubClientFactory->createClientFromBucket($bucket);
        } catch (UserCredentialsNotFound $e) {
            throw new NotificationException('No valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        // Remove previous comments
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
}
