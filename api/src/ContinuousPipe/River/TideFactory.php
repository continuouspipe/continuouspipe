<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\River\Pipeline\Pipeline;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactoryNotFound;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRunner;
use LogStream\LoggerFactory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
     * @param TideConfigurationFactory $configurationFactory
     * @param CommitResolver           $commitResolver
     * @param TaskRunner               $taskRunner
     */
    public function __construct(LoggerFactory $loggerFactory, TaskFactoryRegistry $taskFactoryRegistry, TideConfigurationFactory $configurationFactory, CommitResolver $commitResolver, TaskRunner $taskRunner)
    {
        $this->loggerFactory = $loggerFactory;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
        $this->configurationFactory = $configurationFactory;
        $this->commitResolver = $commitResolver;
        $this->taskRunner = $taskRunner;
    }

    /**
     * Create the pipeline from a generation request.
     *
     * @param Pipeline              $pipeline
     * @param TideGenerationRequest $request
     * @param UuidInterface         $tideUuid
     *
     * @throws TideGenerationException
     * @throws TideConfigurationException
     *
     * @return Tide
     */
    public function create(Pipeline $pipeline, TideGenerationRequest $request, UuidInterface $tideUuid = null) : Tide
    {
        $flow = $request->getFlow();
        $log = $this->loggerFactory->create()->getLog();
        $trigger = $request->getGenerationTrigger();

        $events = new EventCollection();
        $tideUuid = $tideUuid ?: $request->getTargetTideUuid() ?: Uuid::uuid4();
        $tideContext = TideContext::createTide(
            $flow->getUuid(),
            $flow->getTeam(),
            $flow->getUser(),
            $tideUuid,
            $request->getCodeReference(),
            $log,
            $pipeline->getConfiguration(),
            $trigger->getCodeRepositoryEvent()
        );

        $taskList = $this->createTideTaskList($events, $tideContext);

        return Tide::create(
            $this->taskRunner,
            $taskList,
            $tideContext,
            $request,
            FlatPipeline::fromPipeline($pipeline),
            $events
        );
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

        $events = new EventCollection($events);

        $tideCreatedEvent = $tideCreatedEvents[0];
        $tideContext = $tideCreatedEvent->getTideContext();
        $taskList = $this->createTideTaskList($events, $tideContext);

        return Tide::fromEvents($this->taskRunner, $taskList, $events);
    }

    /**
     * @param TideContext     $tideContext
     * @param EventCollection $events
     *
     * @throws TideGenerationException
     *
     * @return TaskList
     */
    private function createTideTaskList(EventCollection $events, TideContext $tideContext)
    {
        $configuration = $tideContext->getConfiguration();
        $tasksConfiguration = array_key_exists('tasks', $configuration) ? $configuration['tasks'] : [];

        $tasks = [];
        foreach ($tasksConfiguration as $taskId => $taskConfig) {
            $taskName = $this->getTaskType($taskId, $taskConfig);
            $taskConfiguration = $taskConfig[$taskName];

            if (is_int($taskId) && isset($taskConfig['identifier'])) {
                $taskId = $taskConfig['identifier'];
            }

            try {
                $taskFactory = $this->taskFactoryRegistry->find($taskName);
            } catch (TaskFactoryNotFound $e) {
                throw new TideGenerationException($e->getMessage(), $e->getCode(), $e);
            }

            $taskContext = TaskContext::createTaskContext(
                new ContextTree(ArrayContext::fromRaw($taskConfiguration ?: []), $tideContext),
                $taskId
            );

            $tasks[] = $taskFactory->create($events, $taskContext, $taskConfiguration);
        }

        return new TaskList($tasks);
    }

    /**
     * @param string $taskId
     * @param array $taskConfig
     *
     * @throws TideGenerationException
     *
     * @return string
     */
    private function getTaskType(string $taskId, array $taskConfig) : string
    {
        $matching = array_filter(array_keys($taskConfig), function (string $key) {
            return !in_array($key, ['filter', 'identifier']);
        });

        if (count($matching) == 0) {
            throw new TideGenerationException(sprintf(
                'Unable to get the type of the task "%s"',
                $taskId
            ));
        } elseif (count($matching) != 1) {
            throw new TideGenerationException(sprintf(
                'Ambiguous type for the task "%s": found %s',
                $taskId,
                implode(', ', $matching)
            ));
        }

        return current($matching);
    }
}
