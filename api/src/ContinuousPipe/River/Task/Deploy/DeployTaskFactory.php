<?php

namespace ContinuousPipe\River\Task\Deploy;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Model\Component\Port;
use ContinuousPipe\River\EventCollection;
use ContinuousPipe\River\Flow\ConfigurationDefinition;
use ContinuousPipe\River\Task\Deploy\Configuration\ComponentFactory;
use ContinuousPipe\River\Task\Deploy\Configuration\Environment;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Task\TaskFactory;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\TideConfigurationException;
use LogStream\LoggerFactory;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

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
    public function __construct(
        MessageBus $commandBus,
        LoggerFactory $loggerFactory,
        ComponentFactory $componentFactory,
        $defaultEnvironmentExpression
    ) {
        $this->commandBus = $commandBus;
        $this->loggerFactory = $loggerFactory;
        $this->componentFactory = $componentFactory;
        $this->defaultEnvironmentExpression = $defaultEnvironmentExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EventCollection $events, TaskContext $taskContext, array $configuration)
    {
        return new DeployTask(
            $events,
            $this->commandBus,
            $this->loggerFactory,
            DeployContext::createDeployContext($taskContext),
            new DeployTaskConfiguration(
                $configuration['cluster'],
                $this->generateServices($taskContext, $configuration['services']),
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
                    ->normalizeKeys(false)
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
                            ->scalarNode('condition')->end()
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
                                    ->arrayNode('command')
                                        ->performNoDeepMerging()
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->append(ConfigurationDefinition::getVariablesNode('environment_variables'))
                                    ->arrayNode('ports')
                                        ->performNoDeepMerging()
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
                                    ->arrayNode('resources')
                                        ->children()
                                            ->arrayNode('requests')
                                                ->children()
                                                    ->scalarNode('cpu')->end()
                                                    ->scalarNode('memory')->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('limits')
                                                ->children()
                                                    ->scalarNode('cpu')->end()
                                                    ->scalarNode('memory')->end()
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
     * @param TaskContext $taskContext
     * @param array       $servicesConfiguration
     *
     * @return array
     */
    private function generateServices(TaskContext $taskContext, array $servicesConfiguration)
    {
        $services = [];

        foreach ($servicesConfiguration as $name => $configuration) {
            if ($this->shouldSkipService($taskContext, $configuration)) {
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
                ->booleanNode('reset')->defaultFalse()->end()
                ->append($this->getProbeNode('liveness_probe'))
                ->append($this->getProbeNode('readiness_probe'))
            ->end();

        return $node;
    }

    private function getProbeNode($name)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $children = $node
            ->children()
                ->scalarNode('type')->defaultValue('http')->end();

        $this->integerOrString($children, 'initial_delay_seconds');
        $this->integerOrString($children, 'timeout_seconds');
        $this->integerOrString($children, 'period_seconds');
        $this->integerOrString($children, 'success_threshold');
        $this->integerOrString($children, 'failure_threshold');
        $this->integerOrString($children, 'port');

        $children
                ->scalarNode('initial_delay_seconds')->end()
                ->scalarNode('path')->end()
                ->scalarNode('host')->end()
                ->scalarNode('scheme')->end()
                ->arrayNode('command')
                    ->performNoDeepMerging()
                    ->prototype('scalar')->end()
                ->end()
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
                    ->arrayNode('annotations')
                        ->normalizeKeys(false)
                        ->requiresAtLeastOneElement()
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('ssl_certificates')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('cert')->isRequired()->end()
                                ->scalarNode('key')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cloud_flare_zone')
                        ->children()
                            ->scalarNode('zone_identifier')->isRequired()->end()
                            ->scalarNode('record_suffix')->isRequired()->end()
                            ->integerNode('ttl')->end()
                            ->booleanNode('proxied')->end()
                            ->arrayNode('authentication')
                                ->isRequired()
                                ->children()
                                    ->scalarNode('email')->isRequired()->end()
                                    ->scalarNode('api_key')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('httplabs')
                        ->children()
                            ->scalarNode('project_identifier')->isRequired()->end()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->arrayNode('middlewares')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @param TaskContext $taskContext
     * @param array       $configuration
     *
     * @return bool
     */
    private function shouldSkipService(TaskContext $taskContext, array $configuration)
    {
        if (array_key_exists('condition', $configuration)) {
            return !$this->isConditionValid($configuration['condition'], new ArrayObject([
                'code_reference' => new ArrayObject([
                    'branch' => $taskContext->getCodeReference()->getBranch(),
                    'sha' => $taskContext->getCodeReference()->getCommitSha(),
                ]),
            ]));
        }

        return $configuration['enabled'] === false;
    }

    /**
     * @param string      $expression
     * @param ArrayObject $context
     *
     * @return string
     *
     * @throws TideConfigurationException
     */
    private function isConditionValid($expression, ArrayObject $context)
    {
        $language = new ExpressionLanguage();

        try {
            return (bool) $language->evaluate($expression, $context->asArray());
        } catch (SyntaxError $e) {
            throw new TideConfigurationException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new TideConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param NodeBuilder $children
     * @param string      $name
     */
    private function integerOrString(NodeBuilder $children, $name)
    {
        $children
            ->variableNode($name)
                ->validate()
                ->always(function ($v) {
                    if (is_string($v) || is_int($v)) {
                        return (int) $v;
                    }

                    throw new InvalidTypeException();
                })
                ->end()
            ->end()
        ;
    }
}
