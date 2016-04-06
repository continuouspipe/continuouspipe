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

    /**
     * @param TaskFactoryRegistry $taskFactoryRegistry
     */
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
                ->append(self::getEnvironmentVariablesNode())
                ->append($this->getTasksNode())
                ->scalarNode('filter')->end()
                ->booleanNode('silent')->defaultFalse()->end()
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
                ->validate()
                    ->always()
                    ->then(function ($value) {
                        $keys = array_filter(array_keys($value), function ($key) {
                            return $key != 'filter';
                        });

                        if (count($keys) == 0) {
                            throw new \InvalidArgumentException('You have to configure a task here, found nothing');
                        } elseif (count($keys) > 1) {
                            throw new \InvalidArgumentException(sprintf(
                                'Only one task should be configured here but found "%s"',
                                implode('" & "', $keys)
                            ));
                        }

                        return $value;
                    })
                ->end()
                ->children();

        foreach ($this->taskFactoryRegistry->findAll() as $factory) {
            $nodeChildren->append($factory->getConfigTree());
        }

        $nodeChildren
                    ->arrayNode('filter')
                        ->children()
                            ->scalarNode('expression')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    public static function getEnvironmentVariablesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('environment_variables');

        $node
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('value')->isRequired()->end()
                    ->scalarNode('condition')->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
