<?php

namespace ContinuousPipe\River\Flow;

use ContinuousPipe\River\Flow\Configuration\KeyIndexedArrayNodeDefinition;
use ContinuousPipe\River\Flow\Configuration\VariablesArrayNodeDefinition;
use ContinuousPipe\River\Task\TaskFactoryRegistry;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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
            ->beforeNormalization()
                ->always(function ($config) {
                    if (is_array($config) && isset($config['environment_variables'])) {
                        // move existing values to the right key
                        $config['variables'] = $config['environment_variables'];

                        // remove invalid key
                        unset($config['environment_variables']);
                    }

                    return $config;
                })
            ->end()
            ->children()
                ->append(self::getVariablesNode('variables'))
                ->append(self::getDefaultsNode())
                ->append($this->getTasksNode())
                ->scalarNode('filter')->end()
                ->booleanNode('silent')->defaultFalse()->end()
                ->append($this->getNotificationsNode())
                ->append($this->getPipelinesNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getTasksNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('tasks');

        $tasksPrototype = $node
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
        ;

        $this->setupTasksPrototype($tasksPrototype);

        $tasksPrototype
            ->end()
        ;

        return $node;
    }

    public static function getVariablesNode($name)
    {
        $nodeBuilder = new NodeBuilder();
        $nodeBuilder->setNodeClass('variables', VariablesArrayNodeDefinition::class);

        $builder = new TreeBuilder();
        $node = $builder->root($name, 'variables', $nodeBuilder);

        $node
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('value')->end()
                    ->scalarNode('condition')->end()
                    ->scalarNode('expression')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private static function getDefaultsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('defaults');

        $node
            ->children()
                ->scalarNode('cluster')->end()
                ->arrayNode('environment')
                    ->children()
                        ->scalarNode('name')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getNotificationsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('notifications');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->beforeNormalization()
                    ->always(function (array $config) {
                        if (isset($config['github_commit_status']) && !isset($config['commit'])) {
                            // move existing values to the right key
                            $config['commit'] = $config['github_commit_status'];

                            // remove invalid key
                            unset($config['github_commit_status']);
                        }

                        if (isset($config['github_pull_request']) && !isset($config['pull_request'])) {
                            // move existing values to the right key
                            $config['pull_request'] = $config['github_pull_request'];

                            // remove invalid key
                            unset($config['github_pull_request']);
                        }

                        return $config;
                    })
                ->end()
                ->children()
                    ->arrayNode('slack')
                        ->children()
                            ->scalarNode('webhook_url')->isRequired()->end()
                        ->end()
                    ->end()
                    ->booleanNode('commit')->end()
                    ->booleanNode('pull_request')->end()
                    ->arrayNode('when')
                        ->defaultValue(['success', 'failure', 'running', 'pending'])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getPipelinesNode()
    {
        $nodeBuilder = new NodeBuilder();
        $nodeBuilder->setNodeClass('key-indexed-array', KeyIndexedArrayNodeDefinition::class);

        $builder = new TreeBuilder();
        $node = $builder->root('pipelines', 'key-indexed-array', $nodeBuilder);

        $tasksPrototype = $node
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('condition')->end()
                    ->append(self::getVariablesNode('variables'))
                    ->node('tasks', 'key-indexed-array')
                        ->isRequired()
                        ->prototype('array')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return array('imports' => $v);
                                })
                            ->end()
                            ->validate()
                                ->always()
                                ->then(function ($value) {
                                    $keys = array_filter(array_keys($value), function ($key) {
                                        return $key != 'filter' && $key != 'imports' && $key != 'identifier';
                                    });

                                    if (count($keys) > 1) {
                                        throw new \InvalidArgumentException(sprintf(
                                            'Only one task type should be configured here but found "%s"',
                                            implode('" & "', $keys)
                                        ));
                                    }

                                    return $value;
                                })
                            ->end()
        ;

        $tasksPrototype
            ->children()
                ->scalarNode('imports')->end()
            ->end()
        ;

        $this->setupTasksPrototype($tasksPrototype);

        $tasksPrototype
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @param $tasksPrototype
     */
    public function setupTasksPrototype($tasksPrototype)
    {
        $nodeChildren = $tasksPrototype

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
            ->end();
    }
}
