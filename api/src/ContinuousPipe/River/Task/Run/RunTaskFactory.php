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
    public function create(TaskContext $taskContext, array $configuration)
    {
        return new RunTask(
            $this->loggerFactory,
            $this->commandBus,
            RunContext::createRunContext($taskContext),
            $this->createConfiguration($configuration)
        );
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
                ->scalarNode('cluster')->isRequired()->end()
                ->arrayNode('image')
                    ->isRequired()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($value) {
                            return ['name' => $value];
                        })
                    ->end()
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('from_service')->end()
                    ->end()
                ->end()
                ->arrayNode('commands')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('environment')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue(null)->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('environment_variables')
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

    /**
     * @param array $configuration
     *
     * @return RunTaskConfiguration
     */
    private function createConfiguration(array $configuration)
    {
        return new RunTaskConfiguration(
            $configuration['cluster'],
            $configuration['image']['name'],
            $configuration['commands'],
            $this->resolveEnvironment($configuration),
            $configuration['environment']['name']
        );
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    private function resolveEnvironment(array $configuration)
    {
        $variables = [];
        foreach ($configuration['environment_variables'] as $item) {
            $variables[$item['name']] = $item['value'];
        }

        return $variables;
    }
}
