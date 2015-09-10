<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use ContinuousPipe\River\Task\TaskList;
use LogStream\LoggerFactory;
use Rhumsaa\Uuid\Uuid;

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
     * @param LoggerFactory       $loggerFactory
     * @param TaskFactoryRegistry $taskFactoryRegistry
     * @param FlowRepository      $flowRepository
     */
    public function __construct(LoggerFactory $loggerFactory, TaskFactoryRegistry $taskFactoryRegistry, FlowRepository $flowRepository)
    {
        $this->loggerFactory = $loggerFactory;
        $this->taskFactoryRegistry = $taskFactoryRegistry;
        $this->flowRepository = $flowRepository;
    }

    /**
     * @param Flow        $flow
     * @param TideContext $tideContext
     *
     * @return Tide
     */
    public function create(Flow $flow, TideContext $tideContext)
    {
        $taskList = $this->createTideTaskList($flow, $tideContext);

        return Tide::create($taskList, $tideContext);
    }

    /**
     * @param Flow          $flow
     * @param CodeReference $codeReference
     *
     * @return Tide
     */
    public function createFromCodeReference(Flow $flow, CodeReference $codeReference)
    {
        $log = $this->loggerFactory->create()->getLog();
        $tideContext = TideContext::createTide($flow->getContext(), Uuid::uuid1(), $codeReference, $log);

        return $this->create($flow, $tideContext);
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
        $flowUuid = $tideContext->getFlowUuid();
        $flow = $this->flowRepository->find($flowUuid);
        $taskList = $this->createTideTaskList($flow, $tideContext);

        return Tide::fromEvents($taskList, $events);
    }

    /**
     * @param Flow        $flow
     * @param TideContext $tideContext
     *
     * @throws Task\TaskFactoryNotFound
     *
     * @return TaskList
     */
    private function createTideTaskList(Flow $flow, TideContext $tideContext)
    {
        $tasks = [];
        foreach ($flow->getTasks() as $taskId => $flowTask) {
            $taskFactory = $this->taskFactoryRegistry->find($flowTask->getName());
            $taskContext = TaskContext::createTaskContext(
                new ContextTree(ArrayContext::fromRaw($flowTask->getContext() ?: []), $tideContext),
                $taskId
            );

            $tasks[] = $taskFactory->create($taskContext);
        }

        return new TaskList($tasks);
    }
}
