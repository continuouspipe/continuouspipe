<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\PullRequestResolver;
use ContinuousPipe\River\Event\GitHub\PullRequestEvent;
use ContinuousPipe\River\Filter\View\TaskListView;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\GitHub\ClientFactory;
use ContinuousPipe\River\TideContext;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
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
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param ClientFactory       $gitHubClientFactory
     * @param LoggerInterface     $logger
     * @param PullRequestResolver $pullRequestResolver
     * @param FlowRepository      $flowRepository
     */
    public function __construct(ClientFactory $gitHubClientFactory, LoggerInterface $logger, PullRequestResolver $pullRequestResolver, FlowRepository $flowRepository)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->logger = $logger;
        $this->pullRequestResolver = $pullRequestResolver;
        $this->flowRepository = $flowRepository;
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
        $flow = FlatFlow::fromFlow($this->flowRepository->find($context->getFlowUuid()));

        if (null !== ($event = $context->getCodeRepositoryEvent()) && $event instanceof PullRequestEvent) {
            $pullRequest = $event->getEvent()->getPullRequest();
        } else {
            $matchingPullRequests = $this->pullRequestResolver->findPullRequestWithHeadReference(
                $flow,
                $context->getCodeReference()
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
            'labels' => $this->getPullRequestLabelNames($flow, $context, $repository, $pullRequest),
        ]);
    }

    /**
     * @param FlatFlow           $flow
     * @param TideContext    $context
     * @param CodeRepository $codeRepository
     * @param PullRequest    $pullRequest
     *
     * @return array
     */
    private function getPullRequestLabelNames(FlatFlow $flow, TideContext $context, CodeRepository $codeRepository, PullRequest $pullRequest)
    {
        if (!$codeRepository instanceof CodeRepository\GitHub\GitHubCodeRepository) {
            return [];
        }

        try {
            $client = $this->gitHubClientFactory->createClientForFlow($flow);
        } catch (UserCredentialsNotFound $e) {
            $this->logger->warning('Unable to get pull-request labels, credentials not found', [
                'exception' => $e,
                'user' => $context->getUser(),
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
