<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Event\GitHub\PullRequestEvent;
use ContinuousPipe\River\Filter\View\TaskListView;
use ContinuousPipe\River\GitHub\UserCredentialsNotFound;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\TideContext;
use ContinuousPipe\River\GitHub\ClientFactory;
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
     * @param ClientFactory   $gitHubClientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(ClientFactory $gitHubClientFactory, LoggerInterface $logger)
    {
        $this->gitHubClientFactory = $gitHubClientFactory;
        $this->logger = $logger;
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

        $context = new ArrayObject([
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
        ]);

        if (null !== ($event = $tideContext->getCodeRepositoryEvent()) && $event instanceof PullRequestEvent) {
            $pullRequest = $event->getEvent()->getPullRequest();

            $context['pull_request'] = new ArrayObject([
                'number' => $pullRequest->getNumber(),
                'state' => $pullRequest->getState(),
                'labels' => $this->getPullRequestLabelNames($tideContext, $event),
            ]);
        } else {
            $context['pull_request'] = new ArrayObject([
                'number' => 0,
                'state' => '',
                'labels' => [],
            ]);
        }

        return $context;
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
     * @param TideContext      $tideContext
     * @param PullRequestEvent $event
     *
     * @return array
     */
    private function getPullRequestLabelNames(TideContext $tideContext, PullRequestEvent $event)
    {
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

        $repository = $event->getEvent()->getRepository();
        try {
            $labels = $client->issue()->labels()->all(
                $repository->getOwner()->getLogin(),
                $repository->getName(),
                $event->getEvent()->getPullRequest()->getNumber()
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
