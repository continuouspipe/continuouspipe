<?php

namespace ContinuousPipe\River\Task\Deploy;

use ContinuousPipe\Model\Component\Port;
use ContinuousPipe\River\Flow\Configuration;
use ContinuousPipe\River\Task\Deploy\Configuration\ComponentFactory;
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
     * @param MessageBus       $commandBus
     * @param LoggerFactory    $loggerFactory
     * @param ComponentFactory $componentFactory
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory, ComponentFactory $componentFactory)
    {
        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->componentFactory = $componentFactory;
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
                $configuration['providerName'],
                $this->generateServices($configuration['services'])
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
                ->scalarNode(DeployContext::PROVIDER_NAME_KEY)->isRequired()->end()
                ->arrayNode('services')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
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
                                            ->integerNode('number_of_replicas')->defaultValue(1)->end()
                                        ->end()
                                    ->end()
                                    ->append(Configuration::getEnvironmentVariablesNode())
                                    ->arrayNode('ports')
                                        ->prototype('array')
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
            $services[] = $this->componentFactory->createFromConfiguration($name, $configuration);
        }

        return $services;
    }
}
