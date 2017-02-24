<?php

namespace ContinuousPipe\River\Task\Run;

use ContinuousPipe\Model\Component\Volume;
use ContinuousPipe\Model\Component\VolumeMount;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use JMS\Serializer\SerializerInterface;
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
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        LoggerFactory $loggerFactory,
        MessageBus $commandBus,
        SerializerInterface $serializer
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->commandBus = $commandBus;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new RunTask(
            $events,
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
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function ($array) {
                            foreach ($array as $key => $value) {
                                if (is_string($key) && !is_array($value)) {
                                    $array[$key] = [
                                        'name' => $key,
                                        'value' => $value,
                                    ];
                                }
                            }
                            return $array;
                        })
                    ->end()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('value')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('volumes')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->isRequired()->end()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('path')->end()
                            ->scalarNode('capacity')->end()
                            ->scalarNode('storage_class')->end()
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
            $configuration['environment']['name'],
            $this->serializer->deserialize(
                \GuzzleHttp\json_encode(isset($configuration['volumes']) ? $configuration['volumes'] : []),
                'array<'.Volume::class.'>',
                'json'
            ),
            $this->serializer->deserialize(
                \GuzzleHttp\json_encode(isset($configuration['volume_mounts']) ? $configuration['volume_mounts'] : []),
                'array<'.VolumeMount::class.'>',
                'json'
            )
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
