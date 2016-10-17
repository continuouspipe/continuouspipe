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
     * @param PullRequestResolver $pullRequestResolver
     * @param ClientFactory       $gitHubClientFactory
     * @param EventStore          $eventStore
     * @param TideRepository      $tideRepository
     */
    public function __construct(PullRequestResolver $pullRequestResolver, ClientFactory $gitHubClientFactory, EventStore $eventStore, TideRepository $tideRepository)
    {
        $this->pullRequestResolver = $pullRequestResolver;
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        if (!in_array($status->getState(), [Status::STATE_SUCCESS, Status::STATE_FAILURE])) {
            return;
        }

        if (!array_key_exists('github_pull_request', $configuration)) {
            throw new NotificationNotSupported('The notifier only supports the "github_pull_request" notification');
        }

        try {
            $client = $this->gitHubClientFactory->createClientFromBucketUuid($tide->getTeam()->getBucketUuid());
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

        $gitHubRepository = $repository->getGitHubRepository();

        // Remove previous comments
        $this->removePreviousComments($client, $gitHubRepository, $tide);

        $pullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference($tide->getCodeReference(), $tide->getTeam());

        foreach ($pullRequests as $pullRequest) {
            // Create the new comment
            $comment = $client->issues()->comments()->create(
                $gitHubRepository->getOwner()->getLogin(),
                $gitHubRepository->getName(),
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

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return array_key_exists('github_pull_request', $configuration) && $configuration['github_pull_request'];
    }
}
