<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class RunTaskFactory implements TaskFactory
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
     * @param LoggerFactory $loggerFactory
     * @param MessageBus    $commandBus
     */
    public function __construct(LoggerFactory $loggerFactory, MessageBus $commandBus)
    {
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TaskContext $taskContext)
    {
        return new RunTask($this->loggerFactory, $this->commandBus, RunContext::createRunContext($taskContext));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('run');

        $node
            ->children()
                ->scalarNode(RunContext::KEY_IMAGE_NAME)->end()
                ->scalarNode(RunContext::KEY_SERVICE_NAME)->end()
                ->arrayNode(RunContext::KEY_COMMANDS)
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('environment')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('value')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
