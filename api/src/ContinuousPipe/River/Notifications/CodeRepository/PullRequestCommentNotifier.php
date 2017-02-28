<?php

namespace ContinuousPipe\River\Notifications\CodeRepository;

use ContinuousPipe\River\CodeRepository\PullRequestCommentManipulator;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Notifications\Events\CommentedPullRequest;
use ContinuousPipe\River\Notifications\Notifier;
use ContinuousPipe\River\Pipe\PublicEndpoint\PublicEndpointWriter;
use ContinuousPipe\River\Tide\Status\Status;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Psr\Log\LoggerInterface;

class PullRequestCommentNotifier implements Notifier
{
    /**
     * @var PullRequestResolver
     */
    private $pullRequestResolver;

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
     * @var PullRequestCommentManipulator
     */
    private $pullRequestCommentManipulator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PullRequestResolver           $pullRequestResolver
     * @param PullRequestCommentManipulator $pullRequestCommentManipulator
     * @param EventStore                    $eventStore
     * @param TideRepository                $tideRepository
     * @param PublicEndpointWriter          $publicEndpointWriter
     * @param LoggerInterface               $logger
     */
    public function __construct(PullRequestResolver $pullRequestResolver, PullRequestCommentManipulator $pullRequestCommentManipulator, EventStore $eventStore, TideRepository $tideRepository, PublicEndpointWriter $publicEndpointWriter, LoggerInterface $logger)
    {
        $this->pullRequestResolver = $pullRequestResolver;
        $this->eventStore = $eventStore;
        $this->tideRepository = $tideRepository;
        $this->publicEndpointWriter = $publicEndpointWriter;
        $this->pullRequestCommentManipulator = $pullRequestCommentManipulator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(Tide $tide, Status $status, array $configuration)
    {
        if (
            (array_key_exists('pull_request', $configuration) && false === $configuration['pull_request'])
                ||
            !in_array($status->getState(), [Status::STATE_SUCCESS, Status::STATE_FAILURE])
                ||
            count($status->getPublicEndpoints()) == 0
        ) {
            return;
        }

        // Remove previous comments
        $this->removePreviousComments($tide);

        $pullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference(
            $tide->getFlowUuid(),
            $tide->getCodeReference()
        );

        foreach ($pullRequests as $pullRequest) {
            // Create the new comment
            $identifier = $this->pullRequestCommentManipulator->writeComment(
                $tide,
                $pullRequest,
                $this->getCommentContents($status)
            );

            $this->eventStore->add(new CommentedPullRequest(
                $tide->getUuid(),
                $pullRequest,
                $identifier
            ));
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
     * @param Tide $tide
     */
    private function removePreviousComments(Tide $tide)
    {
        $tides = $this->tideRepository->findByBranch($tide->getFlowUuid(), $tide->getCodeReference()->getBranch());

        foreach ($tides as $tide) {
            $commentEvents = $this->eventStore->findByTideUuidAndType($tide->getUuid(), CommentedPullRequest::class);

            foreach ($commentEvents as $event) {
                /* @var CommentedPullRequest $event */
                try {
                    $this->pullRequestCommentManipulator->deleteComment(
                        $tide,
                        $event->getPullRequest(),
                        $event->getCommentIdentifier()
                    );
                } catch (\Exception $e) {
                    $this->logger->warning('Unable to remove the comment from the pull-request', [
                        'exception' => $e,
                        'tide_uuid' => (string) $tide->getUuid(),
                    ]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Status $status, array $configuration)
    {
        return $this->pullRequestCommentManipulator->supports($tide);
    }
}
