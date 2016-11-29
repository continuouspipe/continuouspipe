<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
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
    public function create(TaskContext $taskContext, array $configuration)
    {
        return new BuildTask(
            $this->commandBus,
            $this->loggerFactory,
            BuildContext::createBuildContext($taskContext),
            $this->createConfiguration($configuration)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('build');

        $node
            ->beforeNormalization()
                ->ifArray()
                ->then(function (array $configuration) {
                    if (array_key_exists('environment', $configuration)
                        && array_key_exists('services', $configuration)) {
                        foreach ($configuration['services'] as $name => $service) {
                            if (!array_key_exists('environment', $service)) {
                                $configuration['services'][$name]['environment'] = $configuration['environment'];
                            }
                        }

                        unset($configuration['environment']);
                    }

                    return $configuration;
                })
            ->end()
            ->children()
                ->arrayNode('services')
                    ->normalizeKeys(false)
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('image')->isRequired()->end()
                            ->scalarNode('tag')->isRequired()->end()
                            ->scalarNode('build_directory')->defaultNull()->end()
                            ->scalarNode('docker_file_path')->defaultNull()->end()
                            ->enumNode('naming_strategy')
                                ->values(['branch', 'sha1'])
                                ->defaultValue('branch')
                            ->end()
                            ->arrayNode(BuildContext::ENVIRONMENT_KEY)
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                        ->scalarNode('value')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createConfiguration(array $configuration)
    {
        return new BuildTaskConfiguration(
            $this->createServiceConfiguration($configuration['services'])
        );
    }

    /**
     * @param array $buildEnvironment
     *
     * @return array
     */
    private function flattenEnvironmentVariables(array $buildEnvironment)
    {
        $variables = [];
        foreach ($buildEnvironment as $environ) {
            $variables[$environ['name']] = $environ['value'];
        }

        return $variables;
    }

    /**
     * @param array $services
     *
     * @return ServiceConfiguration[]
     */
    private function createServiceConfiguration(array $services)
    {
        return array_map(function (array $service) {
            return new ServiceConfiguration(
                $service['image'],
                $service['tag'],
                $service['build_directory'],
                $service['docker_file_path'],
                $this->flattenEnvironmentVariables($service['environment'] ?: [])
            );
        }, $services);
    }
}
