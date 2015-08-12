<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Task\TaskList;
use ContinuousPipe\River\Task\TaskRegistry;
use LogStream\LoggerFactory;
use Rhumsaa\Uuid\Uuid;

class TideFactory
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var TaskRegistry
     */
    private $taskRegistry;
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @param LoggerFactory  $loggerFactory
     * @param TaskRegistry   $taskRegistry
     * @param FlowRepository $flowRepository
     */
    public function __construct(LoggerFactory $loggerFactory, TaskRegistry $taskRegistry, FlowRepository $flowRepository)
    {
        $this->loggerFactory = $loggerFactory;
        $this->taskRegistry = $taskRegistry;
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
        $taskList = $this->createTideTaskList($flow);

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
        $tideCreatedEvents = array_filter($events, function (TideEvent $event) {
            return $event instanceof TideCreated;
        });

        if (count($tideCreatedEvents) == 0) {
            throw new \RuntimeException('Can\'t recreate a tide from events without the created event');
        }

        $flowUuid = $tideCreatedEvents[0]->getTideContext()->getFlowUuid();
        $flow = $this->flowRepository->find($flowUuid);
        $taskList = $this->createTideTaskList($flow);

        return Tide::fromEvents($taskList, $events);
    }

    /**
     * @param Flow $flow
     *
     * @return TaskList
     *
     * @throws Task\TaskNotFound
     */
    private function createTideTaskList(Flow $flow)
    {
        $tasks = [];
        foreach ($flow->getTasks() as $flowTask) {
            $task = $this->taskRegistry->find($flowTask->getName());
            $task->clear();

            $taskContext = ArrayContext::fromRaw($flowTask->getContext() ?: []);
            $tasks[] = new ContextualizedTask($task, $taskContext);
        }

        return new TaskList($tasks);
    }
}
