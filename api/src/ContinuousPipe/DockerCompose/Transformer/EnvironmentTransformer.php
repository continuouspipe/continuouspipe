<?php

namespace ContinuousPipe\DockerCompose\Transformer;

use ContinuousPipe\Model\Environment;

class EnvironmentTransformer
{
    /**
     * @var ComponentTransformer
     */
    private $componentTransformer;

    /**
     * @param ComponentTransformer $componentTransformer
     */
    public function __construct(ComponentTransformer $componentTransformer)
    {
        $this->componentTransformer = $componentTransformer;
    }

    /**
     * @param string $environmentIdentifier
     * @param array  $parsed
     *
     * @return Environment
     *
     * @throws TransformException
     */
    public function load($environmentIdentifier, array $parsed)
    {
        $components = [];
        foreach ($parsed as $identifier => $parsedComponent) {
            $components[] = $this->componentTransformer->load($identifier, $parsedComponent);
        }

        $environment = new Environment($environmentIdentifier, $environmentIdentifier, $components);

        return $environment;
    }
}
