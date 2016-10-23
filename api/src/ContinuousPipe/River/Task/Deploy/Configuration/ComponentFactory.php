<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Extension;
use JMS\Serializer\Serializer;

class ComponentFactory
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $name
     * @param array  $configuration
     *
     * @return Component
     */
    public function createFromConfiguration($name, array $configuration)
    {
        $component = new Component(
            $name,
            $name,
            $this->getSpecification($configuration),
            $this->getExtensions($configuration),
            [],
            null,
            $this->getDeploymentStrategy($configuration),
            $this->getEndpoints($configuration)
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
    private function getEndpoints(array $configuration)
    {
        if (!array_key_exists('endpoints', $configuration)) {
            return [];
        }
        
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
}
