<?php

namespace ContinuousPipe\River\Task\WebHook;

use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class WebHookTaskFactory implements TaskFactory
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
    public function create(TaskContext $taskContext, array $configuration)
    {
        return new WebHookTask(
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
}
