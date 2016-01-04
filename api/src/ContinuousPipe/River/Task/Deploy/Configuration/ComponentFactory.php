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
            $configuration['locked']
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
