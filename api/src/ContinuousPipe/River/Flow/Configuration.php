<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\Task\TaskFactoryRegistry;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var TaskFactoryRegistry
     */
    private $taskFactoryRegistry;

    public function __construct(TaskFactoryRegistry $taskFactoryRegistry)
    {
        $this->taskFactoryRegistry = $taskFactoryRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('flow');
        $root
            ->children()
                ->arrayNode('environment_variables')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('value')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->getTasksNode())
                ->append($this->getServicesNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getTasksNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('tasks');

        $i = 0;
        $nodeChildren = $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->beforeNormalization()
                ->ifArray()
                ->then(function ($tasks) use (&$i) {
                    foreach ($tasks as $name => &$task) {
                        if (!is_string($name)) {
                            $task['name'] = $i++;
                        }
                    }

                    return $tasks;
                })
            ->end()
            ->prototype('array')
            ->children();

        foreach ($this->taskFactoryRegistry->findAll() as $factory) {
            $nodeChildren->append($factory->getConfigTree());
        }

        $nodeChildren
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getServicesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('services');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('image')->isRequired()->end()
                    ->scalarNode('visibility')->defaultValue('private')->end()
                    ->scalarNode('update')->defaultNull()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
