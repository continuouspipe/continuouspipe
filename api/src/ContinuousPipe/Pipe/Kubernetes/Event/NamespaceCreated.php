<?php

namespace ContinuousPipe\Pipe\Kubernetes\Event;

use ContinuousPipe\Pipe\DeploymentContext;
use Kubernetes\Client\Model\KubernetesNamespace;

class NamespaceCreated
{
    /**
     * @var KubernetesNamespace
     */
    private $namespace;

    /**
     * @var DeploymentContext
     */
    private $context;

    /**
     * @param KubernetesNamespace $namespace
     * @param DeploymentContext   $context
     */
    public function __construct(KubernetesNamespace $namespace, DeploymentContext $context)
    {
        $this->namespace = $namespace;
        $this->context = $context;
    }

    /**
     * @return KubernetesNamespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return DeploymentContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
