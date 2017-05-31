<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Extension;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\CompositeConfigurator;
use ContinuousPipe\River\Task\Deploy\Configuration\Endpoint\EndpointConfigurator;
use ContinuousPipe\River\Task\TaskContext;
use JMS\Serializer\SerializerInterface;

class ComponentFactory
{
    const MAX_HOST_LENGTH = 64;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var EndpointConfigurator
     */
    private $endpointConfigurator;

    public function __construct(EndpointConfigurator $endpointConfigurator, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->endpointConfigurator = $endpointConfigurator;
    }

    /**
     * @parem TaskContext $context
     * @param string $name
     * @param array $configuration
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
        $configuration['endpoints'] = array_map(
            function (array $endpointConfiguration) use ($context) {
                $this->endpointConfigurator->checkConfiguration($endpointConfiguration);

                return $this->endpointConfigurator->addHost($endpointConfiguration, $context);
            },
            $configuration['endpoints']
        );

        $jsonEncodedEndpoints = json_encode($configuration['endpoints']);

        return $this->serializer->deserialize(
            $jsonEncodedEndpoints,
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

}
