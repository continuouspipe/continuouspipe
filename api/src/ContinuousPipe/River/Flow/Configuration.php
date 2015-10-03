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
                ->arrayNode('starts_after')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('push')->defaultValue('true')->end()
                        ->arrayNode('status')
                            ->children()
                                ->scalarNode('context')->isRequired()->end()
                                ->scalarNode('value')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getTasksNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('tasks');

        $nodeChildren = $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
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
}
