<?php

namespace ContinuousPipe\Adapter\Kubernetes\ReverseTransformer;

use Kubernetes\Client\Model\KubernetesObject;

interface ComponentPublicEndpointResolver
{
    public function resolve(KubernetesObject $serviceOrIngress);
}
