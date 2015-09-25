<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration;

use ContinuousPipe\Model\Component;
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
        $jsonEncodedSpecification = json_encode($configuration['specification']);
        $specification = $this->serializer->deserialize($jsonEncodedSpecification, Component\Specification::class, 'json');

        $component = new Component(
            $name,
            $name,
            $specification,
            [],
            [],
            $configuration['locked']
        );

        return $component;
    }
}
