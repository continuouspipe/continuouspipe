<?php

namespace ContinuousPipe\Pipe\Kubernetes\Naming;

use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Environment;
use Kubernetes\Client\Model\KeyValueObjectList;
use Kubernetes\Client\Model\KubernetesNamespace;
use Kubernetes\Client\Model\ObjectMetadata;

interface NamingStrategy
{
    /**
     * @param Component $component
     *
     * @return ObjectMetadata
     */
    public function getObjectMetadataFromComponent(Component $component);

    /**
     * @param Component $component
     *
     * @return KeyValueObjectList
     */
    public function getLabelsByComponent(Component $component);

    /**
     * @param Environment $environment
     *
     * @return KubernetesNamespace
     */
    public function getEnvironmentNamespace(Environment $environment);
}
