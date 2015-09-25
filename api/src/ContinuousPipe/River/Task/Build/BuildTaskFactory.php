<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BuildTaskFactory implements TaskFactory
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param MessageBus    $commandBus
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory)
    {
        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TaskContext $taskContext)
    {
        return new BuildTask($this->commandBus, $this->loggerFactory, BuildContext::createBuildContext($taskContext));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('build');

        $node
            ->children()
                ->arrayNode(BuildContext::ENVIRONMENT_KEY)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('value')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('services')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('image')->isRequired()->end()
                            ->scalarNode('build_directory')->end()
                            ->scalarNode('docker_file_path')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
