<?php

namespace ContinuousPipe\Pipe\Kubernetes\Transformer;

use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Model\KubernetesObject;

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
     * @param Environment $environment
     *
     * @throws TransformationException
     *
     * @return KubernetesObject[]
     */
    public function getElementListFromEnvironment(Environment $environment)
    {
        $objects = [];

        foreach ($environment->getComponents() as $component) {
            $componentObjects = $this->componentTransformer->getElementListFromComponent($component);

            $objects = array_merge($objects, $componentObjects);
        }

        return $objects;
    }
}
