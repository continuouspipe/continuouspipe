<?php

namespace ContinuousPipe\River\Notifications\GitHub\PullRequest;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\Event\GitHub\CommentedTideFeedback;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Notifications\NotificationException;
use ContinuousPipe\River\Notifications\NotificationNotSupported;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Pipe\PublicEndpoint\PublicEndpointWriter;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Github\Client;
use GitHub\WebHook\Model\Repository;

class GitHubPullRequestStatusNotifier implements Notifier
{
    /**
     * @var PullRequestResolver
     */
    private $pullRequestResolver;

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
     * @var PublicEndpointWriter
     */
    private $publicEndpointWriter;

    /**
     * @param PullRequestResolver  $pullRequestResolver
     * @param ClientFactory        $gitHubClientFactory
     * @param EventStore           $eventStore
     * @param TideRepository       $tideRepository
     * @param PublicEndpointWriter $publicEndpointWriter
     */
    public function __construct(PullRequestResolver $pullRequestResolver, ClientFactory $gitHubClientFactory, EventStore $eventStore, TideRepository $tideRepository, PublicEndpointWriter $publicEndpointWriter)
    {
        $this->pullRequestResolver = $pullRequestResolver;
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
        $this->publicEndpointWriter = $publicEndpointWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        if (!in_array($status->getState(), [Status::STATE_SUCCESS, Status::STATE_FAILURE])) {
            return;
        } elseif (count($status->getPublicEndpoints()) == 0) {
            return;
        }

        if (array_key_exists('github_pull_request', $configuration) && !$configuration['github_pull_request']) {
            return;
        }

        try {
            $client = $this->gitHubClientFactory->createClientForFlow($tide->getFlowUuid());
        } catch (UserCredentialsNotFound $e) {
            throw new NotificationException('No valid GitHub credentials in bucket', $e->getCode(), $e);
        }

        $repository = $tide->getCodeReference()->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new NotificationException(sprintf(
                'Repository of type %s is not supported',
                get_class($repository)
            ));
        }

        // Remove previous comments
        $this->removePreviousComments($client, $repository, $tide);

        $pullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference(
            $tide->getFlowUuid(),
            $tide->getCodeReference()
        );

        foreach ($pullRequests as $pullRequest) {
            // Create the new comment
            $comment = $client->issues()->comments()->create(
                $repository->getOrganisation(),
                $repository->getName(),
                $pullRequest->getNumber(),
                [
                    'body' => $this->getCommentContents($status),
                ]
            );

            $this->eventStore->add(new CommentedTideFeedback($tide->getUuid(), $comment['id']));
        }
    }

    /**
     * @param Status $status
     *
     * @return string
     */
    private function getCommentContents(Status $status)
    {
        $publicEndpoints = $status->getPublicEndpoints();
        if (empty($publicEndpoints)) {
            return 'Environment successfully deployed but found no public endpoint.';
        }

        $contents = 'The environment has been successfully deployed, here is the list of public endpoints:'.PHP_EOL;
        foreach ($publicEndpoints as $endpoint) {
            $contents .= '- **'.$endpoint->getName().'**: '.$this->publicEndpointWriter->writeAddress($endpoint).PHP_EOL;
        }

        return $contents;
    }

    /**
     * @param Client               $client
     * @param GitHubCodeRepository $repository
     * @param Tide                 $tide
     */
    private function removePreviousComments(Client $client, GitHubCodeRepository $repository, Tide $tide)
    {
        $tides = $this->tideRepository->findByBranch($tide->getFlowUuid(), $tide->getCodeReference());

        foreach ($tides as $tide) {
            $commentEvents = $this->eventStore->findByTideUuidAndType($tide->getUuid(), CommentedTideFeedback::class);

            foreach ($commentEvents as $event) {
                /* @var CommentedTideFeedback $event */
                try {
                    $client->issues()->comments()->remove(
                        $repository->getOrganisation(),
                        $repository->getName(),
                        $event->getCommentId()
                    );
                } catch (\Exception $e) {
                    // Might be handled better but as this is not really mandatory...
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $tide->getCodeReference()->getRepository() instanceof GitHubCodeRepository;
    }
}
