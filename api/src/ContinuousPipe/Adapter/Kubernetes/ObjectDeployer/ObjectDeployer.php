<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Model\Component\DeploymentStrategy;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\NamespaceClient;

interface ObjectDeployer
{
    /**
     * @param NamespaceClient    $namespaceClient
     * @param KubernetesObject   $object
     * @param DeploymentStrategy $deploymentStrategy
     *
     * @return ComponentCreationStatus
     */
    public function deploy(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null);

    /**
     * @param KubernetesObject $object
     *
     * @return bool
     */
    public function supports(KubernetesObject $object);
}
