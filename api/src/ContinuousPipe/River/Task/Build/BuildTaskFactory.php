<?php

namespace ContinuousPipe\River\Task\Build;

use ContinuousPipe\Builder\BuildRequestCreator;
use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Image;
use ContinuousPipe\Builder\Request\Artifact;
use ContinuousPipe\Builder\Request\BuildRequestStep;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Task\Build\Configuration\ServiceConfiguration;
use ContinuousPipe\River\Task\Task;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use ContinuousPipe\River\Task\TaskRunner;
use ContinuousPipe\River\Task\TaskRunnerException;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideContext;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BuildTaskFactory implements TaskFactory, TaskRunner
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
     * @var BuildRequestCreator
     */
    private $buildRequestCreator;

    /**
     * @param MessageBus          $commandBus
     * @param LoggerFactory       $loggerFactory
     * @param BuildRequestCreator $buildRequestCreator
     */
    public function __construct(MessageBus $commandBus, LoggerFactory $loggerFactory, BuildRequestCreator $buildRequestCreator)
    {
        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->buildRequestCreator = $buildRequestCreator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new BuildTask(
            $events,
            $this->commandBus,
            $this->loggerFactory,
            BuildContext::createBuildContext($taskContext),
            $this->createConfiguration($taskContext, $configuration)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function run(Tide $tide, Task $task)
    {
        if (!$task instanceof BuildTask) {
            throw new TaskRunnerException('This runner only supports build tasks', 0, null, $task);
        }

        return $task->buildImages($this->buildRequestCreator);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Tide $tide, Task $task) : bool
    {
        return $task instanceof BuildTask;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTree()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('build');

        $node = $node
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
                        ->beforeNormalization()
                            ->always()
                            ->then(function ($configuration) {
                                if (!is_array($configuration)) {
                                    return $configuration;
                                }

                                // Stringify the steps identifiers so the Symfony Config Component
                                // merges the defaults of other configs into the same step.
                                if (array_key_exists('steps', $configuration)) {
                                    foreach ($configuration['steps'] as $index => $step) {
                                        if (is_int($index)) {
                                            unset($configuration['steps'][$index]);
                                            $configuration['steps']['0'.$index] = $step;
                                        }
                                    }
                                }

                                return $configuration;
                            })
                        ->end()
                        ->children();
        $this->addBuildImageChildren($node);
        $node = $node
                            ->arrayNode('steps')
                                ->useAttributeAsKey('index')
                                ->normalizeKeys(false)
                                ->prototype('array')
                                    ->children();
        $this->addBuildImageChildren($node);
        $node = $node
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



    private function createConfiguration(TideContext $context, array $configuration)
    {
        return new BuildTaskConfiguration(
            $this->createServiceConfiguration($context, $configuration['services'])
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
     * @param TideContext $context
     * @param array       $services
     *
     * @return ServiceConfiguration[]
     */
    private function createServiceConfiguration(TideContext $context, array $services)
    {
        return array_map(function (array $serviceConfiguration) use ($context) {
            // If no step defined, then we simply use the first one.
            if (!isset($serviceConfiguration['steps']) || empty($serviceConfiguration['steps'])) {
                $serviceConfiguration = [
                    'steps' => [
                        $serviceConfiguration,
                    ],
                ];
            }

            return new ServiceConfiguration(array_map(function (array $stepConfiguration) use ($context) {
                return $this->transformStep($context, $stepConfiguration);
            }, array_values($serviceConfiguration['steps'])));
        }, $services);
    }

    /**
     * @param TideContext $context
     * @param array $stepConfiguration
     * @return BuildRequestStep
     */
    private function transformStep(TideContext $context, array $stepConfiguration) : BuildRequestStep
    {
        $step = (new BuildRequestStep())
            ->withContext(new Context($stepConfiguration['docker_file_path'], $stepConfiguration['build_directory']))
            ->withEnvironment($this->flattenEnvironmentVariables($stepConfiguration['environment'] ?: []))
        ;

        if (isset($stepConfiguration['read_artifacts'])) {
            $step = $step->withReadArtifacts(array_map(function (array $artifactConfiguration) use ($context) {
                return $this->transformArtifact($context, $artifactConfiguration);
            }, $stepConfiguration['read_artifacts']));
        }

        if (isset($stepConfiguration['write_artifacts'])) {
            $step = $step->withWriteArtifacts(array_map(function (array $artifactConfiguration) use ($context) {
                return $this->transformArtifact($context, $artifactConfiguration);
            }, $stepConfiguration['write_artifacts']));
        }

        if (isset($stepConfiguration['image']) && isset($stepConfiguration['tag'])) {
            $step = $step->withImage(new Image($stepConfiguration['image'], $stepConfiguration['tag']));
        }

        if (isset($stepConfiguration['cache'])) {
            $cacheArtifacts = array_map(function (array $cacheConfiguration) use ($context) {
                return $this->createCacheArtifact($context, $cacheConfiguration);
            }, $stepConfiguration['cache']);

            $step = $step
                ->withReadArtifacts(array_merge($step->getReadArtifacts(), $cacheArtifacts))
                ->withWriteArtifacts(array_merge($step->getWriteArtifacts(), $cacheArtifacts))
            ;
        }

        return $step;
    }

    private function transformArtifact(TideContext $context, array $artifactConfiguration) : Artifact
    {
        return new Artifact(
            $context->getTideUuid()->toString() . '-' . $artifactConfiguration['name'],
            $artifactConfiguration['path']
        );
    }

    private function createCacheArtifact(TideContext $context, array $cacheConfiguration)
    {
        return new Artifact(
            $context->getFlowUuid()->toString() . '-' . $cacheConfiguration['identifier'],
            $cacheConfiguration['path'],
            true
        );
    }

    /**
     * Return a validator callback
     *
     * Docker image name reference @link https://github.com/docker/distribution/blob/master/reference/regexp.go#L53-L56.
     *
     * @return \Closure
     */
    private function getDockerImageNameValidator()
    {
        return function ($imageName) {
            $domainComponentRegexp = '(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9])';
            $optionalDotRegexp = '(\.' . $domainComponentRegexp . ')?';
            $optionalPortRegexp = '(?::[0-9]+)?';
            $domainRegexp = $domainComponentRegexp . $optionalDotRegexp . $optionalPortRegexp;
            $alphaNumericRegexp = '[a-z0-9]+';
            $optionalSeparatorRegexp = '(?:[._]|__|[-]*)';
            $nameComponentRegexp = $alphaNumericRegexp . '(?:'. $optionalSeparatorRegexp . $alphaNumericRegexp .')*';
            $pattern =
                '#^'.
                '(?:' . $domainRegexp . '\/)?' .
                $nameComponentRegexp .
                '(?:\/' . $nameComponentRegexp . ')*' .
                '$#';

            return 1 !== preg_match($pattern, $imageName);
        };
    }

    /**
     * Return a validator callback
     *
     * Docker image tag reference @link https://github.com/docker/distribution/blob/master/reference/regexp.go#L37.
     *
     * @return \Closure
     */
    private function getDockerImageTagValidator()
    {
        return function ($imageName) {
            $tagRegexp = '[\w][\w.-]{0,127}';
            $pattern =
                '#^'.
                $tagRegexp .
                '$#';

            return 1 !== preg_match($pattern, $imageName);
        };
    }

    private function addBuildImageChildren(NodeBuilder $node)
    {
        $node
            ->scalarNode('image')
                ->validate()
                    ->ifTrue($this->getDockerImageNameValidator())
                    ->thenInvalid('The name %s of the Docker image is invalid.')
                ->end()
            ->end()
            ->scalarNode('tag')
                ->validate()
                    ->ifTrue($this->getDockerImageTagValidator())
                    ->thenInvalid('The tag %s of the Docker image is invalid.')
                ->end()
            ->end()
            ->scalarNode('build_directory')->defaultNull()->end()
            ->scalarNode('docker_file_path')->defaultNull()->end()
            ->enumNode('naming_strategy')
                ->values(['branch', 'sha1'])
                ->defaultValue('branch')
            ->end()
            ->arrayNode(BuildContext::ENVIRONMENT_KEY)
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
            ->append($this->artifactsNode('read_artifacts'))
            ->append($this->artifactsNode('write_artifacts'))
            ->arrayNode('cache')
                ->prototype('array')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($path) {
                            return [
                                'identifier' => md5($path),
                                'path' => $path,
                            ];
                        })
                    ->end()
                    ->children()
                        ->scalarNode('identifier')->isRequired()->end()
                        ->scalarNode('path')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function artifactsNode(string $name)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('path')->isRequired()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
