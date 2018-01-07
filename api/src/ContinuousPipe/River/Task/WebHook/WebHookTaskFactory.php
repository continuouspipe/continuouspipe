<?php

namespace ContinuousPipe\River\Task\WebHook;

use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\WebHook\WebHookClient;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class WebHookTaskFactory implements TaskFactory, TaskRunner
{
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @var MessageBus
     */
    private $commandBus;
    /**
     * @var WebHookClient
     */
    private $webHookClient;

    /**
     * @param LoggerFactory $loggerFactory
     * @param MessageBus    $commandBus
     * @param WebHookClient $webHookClient
     */
    public function __construct(LoggerFactory $loggerFactory, MessageBus $commandBus, WebHookClient $webHookClient)
    {
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->webHookClient = $webHookClient;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new WebHookTask(
            $events,
            $taskContext,
            $this->loggerFactory,
            $this->commandBus,
            $configuration
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('web_hook');

        $node
            ->children()
                ->scalarNode('url')->isRequired()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (!$task instanceof WebHookTask) {
            throw new TaskRunnerException('This runner only supports WebHook tasks', 0, null, $task);
        }

        return $task->send($this->webHookClient);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task): bool
    {
        return $task instanceof WebHookTask;
    }
}
