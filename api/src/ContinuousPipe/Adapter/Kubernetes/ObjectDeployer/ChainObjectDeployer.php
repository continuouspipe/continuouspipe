<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use ContinuousPipe\Model\Component\DeploymentStrategy;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\NamespaceClient;

class ChainObjectDeployer implements ObjectDeployer
{
    /**
     * @var ObjectDeployer[]
     */
    private $objectDeployers;

    /**
     * @param ObjectDeployer[] $objectDeployers
     */
    public function __construct(array $objectDeployers = [])
    {
        $this->objectDeployers = $objectDeployers;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null)
    {
        foreach ($this->objectDeployers as $objectDeployer) {
            if ($objectDeployer->supports($object)) {
                return $objectDeployer->deploy($namespaceClient, $object, $deploymentStrategy);
            }
        }

        throw new \RuntimeException('The deployment is not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        foreach ($this->objectDeployers as $objectDeployer) {
            if ($objectDeployer->supports($object)) {
                return true;
            }
        }

        return false;
    }
}
