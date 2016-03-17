<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Model\Component\Port;
use ContinuousPipe\River\Flow\Configuration;
use ContinuousPipe\River\Task\Deploy\Configuration\ComponentFactory;
use ContinuousPipe\River\Task\Deploy\Configuration\Environment;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DeployTaskFactory implements TaskFactory
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
     * @var ComponentFactory
     */
    private $componentFactory;

    /**
     * @var string
     */
    private $defaultEnvironmentExpression;

    /**
     * @param MessageBus       $commandBus
     * @param LoggerFactory    $loggerFactory
     * @param ComponentFactory $componentFactory
     * @param string           $defaultEnvironmentExpression
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory, ComponentFactory $componentFactory, $defaultEnvironmentExpression)
    {
        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->componentFactory = $componentFactory;
        $this->defaultEnvironmentExpression = $defaultEnvironmentExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TaskContext $taskContext, array $configuration)
    {
        return new DeployTask(
            $this->commandBus,
            $this->loggerFactory,
            DeployContext::createDeployContext($taskContext),
            new DeployTaskConfiguration(
                $configuration['cluster'],
                $this->generateServices($configuration['services']),
                $configuration['environment']['name']
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('deploy');

        $node
            ->children()
                ->scalarNode('cluster')->isRequired()->end()
                ->arrayNode('environment')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('services')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->canBeEnabled()
                        ->children()
                            ->arrayNode('specification')
                                ->isRequired()
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('source')
                                        ->isRequired()
                                        ->children()
                                            ->scalarNode('image')->isRequired()->end()
                                            ->scalarNode('tag')->defaultNull()->end()
                                            ->scalarNode('repository')->end()
                                            ->scalarNode('from_service')->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('accessibility')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('from_cluster')->defaultTrue()->end()
                                            ->scalarNode('from_external')->defaultFalse()->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('scalability')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->booleanNode('enabled')->defaultTrue()->end()
                                            ->integerNode('number_of_replicas')->defaultNull()->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('runtime_policy')
                                        ->children()
                                            ->booleanNode('privileged')->defaultFalse()->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('volumes')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('type')->isRequired()->end()
                                                ->scalarNode('name')->isRequired()->end()
                                                ->scalarNode('path')->end()
                                                ->scalarNode('capacity')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('volume_mounts')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('name')->isRequired()->end()
                                                ->scalarNode('mount_path')->isRequired()->end()
                                                ->booleanNode('read_only')->defaultFalse()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('command')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->append(Configuration::getEnvironmentVariablesNode())
                                    ->arrayNode('ports')
                                        ->prototype('array')
                                            ->beforeNormalization()
                                                ->ifTrue(function ($value) {
                                                    return is_int($value);
                                                })
                                                ->then(function ($port) {
                                                    return [
                                                        'port' => $port,
                                                        'identifier' => sprintf('port-%d', $port),
                                                    ];
                                                })
                                            ->end()
                                            ->children()
                                                ->scalarNode('identifier')->isRequired()->end()
                                                ->integerNode('port')->isRequired()->end()
                                                ->enumNode('protocol')
                                                    ->values([Port::PROTOCOL_TCP, Port::PROTOCOL_UDP])
                                                    ->defaultValue(Port::PROTOCOL_TCP)
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('extensions')
                                ->children()
                                    ->arrayNode('reverse_proxy')
                                        ->children()
                                            ->arrayNode('domain_names')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->booleanNode('locked')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @param array $servicesConfiguration
     *
     * @return array
     */
    private function generateServices(array $servicesConfiguration)
    {
        $services = [];

        foreach ($servicesConfiguration as $name => $configuration) {
            if ($configuration['enabled'] === false) {
                continue;
            }

            $services[] = $this->componentFactory->createFromConfiguration($name, $configuration);
        }

        return $services;
    }
}
