<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Extension;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\CompositeConfigurator;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\EndpointConfigurationEnhancer;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\EndpointConfigurator;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\Tide\Configuration\ArrayObject;
use ContinuousPipe\River\TideConfigurationException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ComponentFactory
{
    const MAX_HOST_LENGTH = 64;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var EndpointConfigurationEnhancer
     */
    private $endpointConfigurationEnhancer;

    public function __construct(EndpointConfigurationEnhancer $endpointConfigurationEnhancer, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->endpointConfigurationEnhancer = $endpointConfigurationEnhancer;
    }

    /**
     * @parem TaskContext $context
     * @param string $name
     * @param array $configuration
     *
     * @return Component|null
     */
    public function createFromConfiguration(TaskContext $context, string $name, array $configuration)
    {
        if ($this->shouldSkipService($context, $configuration)) {
            return null;
        }

        $component = new Component(
            $name,
            $name,
            $this->getSpecification($configuration),
            $this->getExtensions($configuration),
            [],
            null,
            $this->getDeploymentStrategy($configuration),
            $this->getEndpoints($context, $configuration)
        );

        return $component;
    }

    /**
     * Get component specification from the configuration.
     *
     * @param array $configuration
     *
     * @return Component\Specification
     */
    private function getSpecification(array $configuration)
    {
        $jsonEncodedSpecification = json_encode($configuration['specification']);

        return $this->serializer->deserialize($jsonEncodedSpecification, Component\Specification::class, 'json');
    }

    /**
     * @return Component\Endpoint[]
     */
    private function getEndpoints(TaskContext $context, array $configuration)
    {
        if (!array_key_exists('endpoints', $configuration)) {
            return [];
        }

        $endpoints = array_values(array_filter($configuration['endpoints'], function (array $endpoint) use ($context) {
            if (!isset($endpoint['condition'])) {
                return true;
            }

            return $this->isConditionValid($endpoint['condition'], $context);
        }));

        return $this->serializer->deserialize(
            json_encode($this->addHostsToConfig($context, $endpoints)),
            sprintf('array<%s>', Component\Endpoint::class),
            'json'
        );
    }

    /**
     * Get component deployment strategy from the configuration.
     *
     * @param array $configuration
     *
     * @return Component\DeploymentStrategy|null
     */
    private function getDeploymentStrategy(array $configuration)
    {
        if (!array_key_exists('deployment_strategy', $configuration)) {
            return;
        }

        $jsonEncoded = json_encode($configuration['deployment_strategy']);

        return $this->serializer->deserialize($jsonEncoded, Component\DeploymentStrategy::class, 'json');
    }

    /**
     * Returns the deserialized extension objects.
     *
     * @param array $configuration
     *
     * @return Extension[]
     */
    private function getExtensions(array $configuration)
    {
        if (!array_key_exists('extensions', $configuration)) {
            return [];
        }

        $normalizedExtensions = [];
        foreach ($configuration['extensions'] as $name => $extension) {
            $extension['name'] = $name;

            $normalizedExtensions[] = $extension;
        }

        $jsonEncodedExtensions = json_encode($normalizedExtensions);

        return $this->serializer->deserialize(
            $jsonEncodedExtensions,
            sprintf('array<%s>', Extension::class),
            'json'
        );
    }

    private function addHostsToConfig(TaskContext $context, array $configuration)
    {
        return array_map(
            function (array $endpointConfiguration) use ($context) {
                return $this->endpointConfigurationEnhancer->enhance($endpointConfiguration, $context);
            },
            $configuration
        );
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
            return !$this->isConditionValid($configuration['condition'], $taskContext);
        }

        return $configuration['enabled'] === false;
    }

    /**
     * @param string      $expression
     * @param TaskContext $taskContext
     *
     * @return string
     *
     * @throws TideConfigurationException
     */
    private function isConditionValid($expression, TaskContext $taskContext)
    {
        $language = new ExpressionLanguage();
        $context =  new ArrayObject([
            'code_reference' => new ArrayObject([
                'branch' => $taskContext->getCodeReference()->getBranch(),
                'sha' => $taskContext->getCodeReference()->getCommitSha(),
            ]),
        ]);

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
}
