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
                        ->beforeNormalization()
                            ->always()
                            ->then(function ($array) {
                                if (isset($array['locked'])) {
                                    if (!array_key_exists('deployment_strategy', $array)) {
                                        $array['deployment_strategy'] = [];
                                    }

                                    if (!array_key_exists('locked', $array['deployment_strategy'])) {
                                        $array['deployment_strategy']['locked'] = $array['locked'];
                                    }

                                    unset($array['locked']);
                                }

                                return $array;
                            })
                        ->end()
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
                            ->append($this->getServiceEndpointsNode())
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
                            ->append($this->getDeploymentStrategyNode())
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

    private function getDeploymentStrategyNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('deployment_strategy');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('locked')->defaultFalse()->end()
                ->booleanNode('attached')->defaultFalse()->end()
                ->append($this->getProbeNode('liveness_probe'))
                ->append($this->getProbeNode('readiness_probe'))
            ->end();

        return $node;
    }

    private function getProbeNode($name)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->children()
                ->scalarNode('type')->defaultValue('http')->end()
                ->integerNode('initial_delay_seconds')->end()
                ->integerNode('timeout_seconds')->end()
                ->integerNode('period_seconds')->end()
                ->integerNode('success_threshold')->end()
                ->integerNode('failure_threshold')->end()
                ->scalarNode('path')->end()
                ->integerNode('port')->end()
                ->scalarNode('host')->end()
                ->scalarNode('scheme')->end()
            ->end()
        ;

        return $node;
    }

    private function getServiceEndpointsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('endpoints');

        $node
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('type')->defaultNull()->end()
                    ->arrayNode('ssl_certificates')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('cert')->isRequired()->end()
                                ->scalarNode('key')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
