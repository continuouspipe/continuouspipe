<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\Event\GitHub\PullRequestEvent;
use ContinuousPipe\River\Filter\View\TaskListView;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\TideContext;
use ContinuousPipe\River\GitHub\ClientFactory;
use GitHub\WebHook\Model\PullRequest;
use Psr\Log\LoggerInterface;

class ContextFactory
{
    /**
     * @var ClientFactory
     */
    private $gitHubClientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PullRequestResolver
     */
    private $pullRequestResolver;

    /**
     * @param ClientFactory       $gitHubClientFactory
     * @param LoggerInterface     $logger
     * @param PullRequestResolver $pullRequestResolver
     */
    public function __construct(ClientFactory $gitHubClientFactory, LoggerInterface $logger, PullRequestResolver $pullRequestResolver)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->logger = $logger;
        $this->pullRequestResolver = $pullRequestResolver;
    }

    /**
     * Create the context available in tasks' filters.
     *
     * @param Tide $tide
     *
     * @return ArrayObject
     */
    public function create(Tide $tide)
    {
        $tideContext = $tide->getContext();

        return new ArrayObject([
            'code_reference' => new ArrayObject([
                'branch' => $tideContext->getCodeReference()->getBranch(),
                'sha' => $tideContext->getCodeReference()->getCommitSha(),
            ]),
            'tide' => new ArrayObject([
                'uuid' => (string) $tideContext->getTideUuid(),
            ]),
            'flow' => new ArrayObject([
                'uuid' => (string) $tideContext->getFlowUuid(),
            ]),
            'tasks' => $this->createTasksView($tide->getTasks()->getTasks()),
            'pull_request' => $this->getPullRequestContext($tide),
        ]);
    }

    /**
     * @param Task[] $tasks
     *
     * @return object
     */
    private function createTasksView(array $tasks)
    {
        $view = new TaskListView();

        foreach ($tasks as $task) {
            $taskId = $task->getContext()->getTaskId();

            $view->add($taskId, $task->getExposedContext());
        }

        return $view;
    }

    /**
     * @param Tide $tide
     *
     * @return ArrayObject
     */
    private function getPullRequestContext(Tide $tide)
    {
        $context = $tide->getContext();
        $repository = $context->getCodeRepository();

        if (null !== ($event = $context->getCodeRepositoryEvent()) && $event instanceof PullRequestEvent) {
            $pullRequest = $event->getEvent()->getPullRequest();
        } else {
            $matchingPullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference(
                $context->getCodeReference(),
                $context->getTeam()
            );

            $pullRequest = count($matchingPullRequests) > 0 ? current($matchingPullRequests) : null;
        }

        if (null === $pullRequest) {
            return new ArrayObject([
                'number' => 0,
                'state' => '',
                'labels' => [],
            ]);
        }

        return new ArrayObject([
            'number' => $pullRequest->getNumber(),
            'state' => $pullRequest->getState(),
            'labels' => $this->getPullRequestLabelNames($context, $repository, $pullRequest),
        ]);
    }

    /**
     * @param TideContext $tideContext
     * @param PullRequest $pullRequest
     *
     * @return array
     */
    private function getPullRequestLabelNames(TideContext $tideContext, CodeRepository $codeRepository, PullRequest $pullRequest)
    {
        if (!$codeRepository instanceof CodeRepository\GitHub\GitHubCodeRepository) {
            return [];
        }

        $user = $tideContext->getUser();

        try {
            $client = $this->gitHubClientFactory->createClientFromBucketUuid($tideContext->getTeam()->getBucketUuid());
        } catch (UserCredentialsNotFound $e) {
            $this->logger->warning('Unable to get pull-request labels, credentials not found', [
                'exception' => $e,
                'user' => $user,
            ]);

            return [];
        }

        try {
            $repository = $codeRepository->getGitHubRepository();
            $labels = $client->issue()->labels()->all(
                $repository->getOwner()->getLogin(),
                $repository->getName(),
                $pullRequest->getNumber()
            );
        } catch (\Exception $e) {
            $this->logger->error('Unable to get pull-request labels, the communication with the GH API wasn\'t successful', [
                'exception' => $e,
            ]);

            return [];
        }

        if (!is_array($labels)) {
            $this->logger->error('Received a non-array response from GH API', [
                'response' => $labels,
            ]);

            return [];
        }

        return array_map(function (array $label) {
            return $label['name'];
        }, $labels);
    }
}
