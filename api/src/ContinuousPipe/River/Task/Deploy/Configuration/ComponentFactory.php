<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration;

use Cocur\Slugify\Slugify;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Extension;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\TideConfigurationException;
use JMS\Serializer\SerializerInterface;

class ComponentFactory
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var FlowVariableResolver
     */
    private $flowVariableResolver;

    public function __construct(FlowVariableResolver $flowVariableResolver, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->flowVariableResolver = $flowVariableResolver;
    }

    /**
     * @parem TaskContext $context
     * @param string      $name
     * @param array       $configuration
     *
     * @return Component
     */
    public function createFromConfiguration(TaskContext $context, string $name, array $configuration)
    {
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
     * Get component endpoints from the configuration.
     *
     * @param array $configuration
     *
     * @return Component\Endpoint[]
     */
    private function getEndpoints(TaskContext $context, array $configuration)
    {
        if (!array_key_exists('endpoints', $configuration)) {
            return [];
        }

        // Resolve hosts expression
        $configuration['endpoints'] = array_map(function (array $endpointConfiguration) use ($context) {
            if (isset($endpointConfiguration['ingress']['host'])) {
                $endpointConfiguration['ingress']['rules'] = [
                    $this->transformIngressHostIntoRule($context, $endpointConfiguration['ingress']['host']),
                ];
            }

            return $endpointConfiguration;
        }, $configuration['endpoints']);

        $jsonEncodedEndpoints = json_encode($configuration['endpoints']);

        return $this->serializer->deserialize($jsonEncodedEndpoints, sprintf('array<%s>', Component\Endpoint::class), 'json');
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

    private function transformIngressHostIntoRule(TaskContext $context, array $hostConfiguration)
    {
        if (!isset($hostConfiguration['expression'])) {
            throw new TideGenerationException('The ingress host needs an expression');
        }

        try {
            $resolvedHostname = (new Slugify(['regexp' => '/([^A-Za-z0-9\.]|-)+/']))->slugify($this->flowVariableResolver->resolveExpression(
                $hostConfiguration['expression'],
                $this->flowVariableResolver->createContext($context->getFlowUuid(), $context->getCodeReference())
            ));
        } catch (TideConfigurationException $e) {
            throw new TideGenerationException($e->getMessage(), $e->getCode(), $e);
        }

        return [
            'host' => $resolvedHostname,
        ];
    }
}
