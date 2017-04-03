<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentCreationStatus;
use ContinuousPipe\Adapter\Kubernetes\Component\ComponentException;
use ContinuousPipe\Adapter\Kubernetes\Inspector\PodInspector;
use ContinuousPipe\Model\Component\DeploymentStrategy;
use Kubernetes\Client\Exception\PodNotFound;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\NamespaceClient;

class PodObjectDeployer extends AbstractObjectDeployer
{
    /**
     * @var PodInspector
     */
    private $podInspector;

    public function __construct(PodInspector $podInspector)
    {
        $this->podInspector = $podInspector;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null)
    {
        if (!$object instanceof Pod) {
            throw new \InvalidArgumentException('This deployer only supports `Pod` objects');
        }

        $repository = $this->getRepository($namespaceClient, $object);
        $name = $object->getMetadata()->getName();
        $deleted = [];

        try {
            $existingPod = $repository->findOneByName($name);
            if ($this->podInspector->isRunningAndReady($existingPod)) {
                throw new ComponentException(sprintf(
                    'A running pod named "%s" was found',
                    $name
                ));
            }

            $repository->delete($existingPod);
        } catch (PodNotFound $e) {
            // The pod do not exists, perfect.
        }

        $created = [
            $this->create($namespaceClient, $object),
        ];

        return new ComponentCreationStatus($created, [], $deleted, []);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Pod;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getPodRepository();
    }
}
