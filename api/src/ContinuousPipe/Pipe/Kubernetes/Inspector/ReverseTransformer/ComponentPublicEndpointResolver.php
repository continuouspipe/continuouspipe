<?php

namespace ContinuousPipe\Pipe\Kubernetes\Inspector\ReverseTransformer;

use Kubernetes\Client\Model\KubernetesObject;

interface ComponentPublicEndpointResolver
{
    /**
     * @param KubernetesObject $serviceOrIngress
     *
     * @return string[]
     */
    public function resolve(KubernetesObject $serviceOrIngress) : array;
}
