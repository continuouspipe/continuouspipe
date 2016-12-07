<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideValidated;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Tide\Request\TideCreationRequest;
use ContinuousPipe\River\Flow\Projections\FlatFlow as FlowView;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Ramsey\Uuid\Uuid;

class TideFactory
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TaskFactoryRegistry
     */
    private $taskFactoryRegistry;

    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var TideConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var CommitResolver
     */
    private $commitResolver;

    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * @param LoggerFactory            $loggerFactory
     * @param TaskFactoryRegistry      $taskFactoryRegistry
     * @param FlowRepository           $flowRepository
     * @param TideConfigurationFactory $configurationFactory
     * @param CommitResolver           $commitResolver
     * @param TaskRunner               $taskRunner
     */
    public function __construct(LoggerFactory $loggerFactory, TaskFactoryRegistry $taskFactoryRegistry, FlowRepository $flowRepository, TideConfigurationFactory $configurationFactory, CommitResolver $commitResolver, TaskRunner $taskRunner)
    {
        $this->loggerFactory = $loggerFactory;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
        $this->flowRepository = $flowRepository;
        $this->configurationFactory = $configurationFactory;
        $this->commitResolver = $commitResolver;
        $this->taskRunner = $taskRunner;
    }

    /**
     * @param Flow                $flow
     * @param TideCreationRequest $creationRequest
     *
     * @throws CommitResolverException
     *
     * @return Tide
     */
    public function createFromCreationRequest(Flow $flow, TideCreationRequest $creationRequest)
    {
        $repository = $flow->getCodeRepository();
        if (null == ($sha1 = $creationRequest->getSha1())) {
            $sha1 = $this->commitResolver->getHeadCommitOfBranch(FlowView::fromFlow($flow), $creationRequest->getBranch());
        }

        return $this->createFromCodeReference($flow, new CodeReference(
            $repository,
            $sha1,
            $creationRequest->getBranch()
        ));
    }

    /**
     * @param Flow                $flow
     * @param CodeReference       $codeReference
     * @param CodeRepositoryEvent $codeRepositoryEvent
     * @param Uuid                $tideUuid
     *
     * @return Tide
     */
    public function createFromCodeReference(Flow $flow, CodeReference $codeReference, CodeRepositoryEvent $codeRepositoryEvent = null, Uuid $tideUuid = null)
    {
        $log = $this->loggerFactory->create()->getLog();
        $tideUuid = $tideUuid ?: Uuid::uuid1();
        $extraEvents = [];

        try {
            $configuration = $this->configurationFactory->getConfiguration($flow, $codeReference);

            $extraEvents[] = new TideValidated($tideUuid);
        } catch (TideConfigurationException $e) {
            $configuration = [];

            $logger = $this->loggerFactory->from($log);
            $logger->child(new Text(sprintf(
                'Unable to create tide task list: %s',
                $e->getMessage()
            )));

            $extraEvents[] = new TideFailed($tideUuid, $e->getMessage());
        }

        $tideContext = TideContext::createTide(
            $flow->getContext(),
            $tideUuid,
            $codeReference,
            $log,
            $configuration,
            $codeRepositoryEvent
        );

        $taskList = $this->createTideTaskList($tideContext);

        $tide = Tide::create($this->taskRunner, $taskList, $tideContext);
        foreach ($extraEvents as $event) {
            $tide->pushNewEvent($event);
        }

        return $tide;
    }

    /**
     * @param TideEvent[] $events
     *
     * @return Tide
     */
    public function createFromEvents(array $events)
    {
        /** @var TideCreated[] $tideCreatedEvents */
        $tideCreatedEvents = array_values(array_filter($events, function (TideEvent $event) {
            return $event instanceof TideCreated;
        }));

        if (count($tideCreatedEvents) == 0) {
            throw new \RuntimeException('Can\'t recreate a tide from events without the created event');
        }

        $tideCreatedEvent = $tideCreatedEvents[0];
        $tideContext = $tideCreatedEvent->getTideContext();
        $taskList = $this->createTideTaskList($tideContext);

        return Tide::fromEvents($this->taskRunner, $taskList, $events);
    }

    /**
     * @param TideContext $tideContext
     *
     * @throws Task\TaskFactoryNotFound
     *
     * @return TaskList
     */
    private function createTideTaskList(TideContext $tideContext)
    {
        $configuration = $tideContext->getConfiguration();
        $tasksConfiguration = array_key_exists('tasks', $configuration) ? $configuration['tasks'] : [];

        $tasks = [];
        foreach ($tasksConfiguration as $taskId => $taskConfig) {
            $taskName = array_keys($taskConfig)[0];
            $taskConfiguration = $taskConfig[$taskName];

            $taskFactory = $this->taskFactoryRegistry->find($taskName);
            $taskContext = TaskContext::createTaskContext(
                new ContextTree(ArrayContext::fromRaw($taskConfiguration ?: []), $tideContext),
                $taskId
            );

            $tasks[] = $taskFactory->create($taskContext, $taskConfiguration);
        }

        return new TaskList($tasks);
    }
}
