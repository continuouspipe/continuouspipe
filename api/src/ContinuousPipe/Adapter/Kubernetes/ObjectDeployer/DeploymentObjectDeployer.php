<?php

namespace ContinuousPipe\Adapter\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\NamespaceClient;

class DeploymentObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Deployment;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getDeploymentRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function create(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        $deployment = $this->getRepository($namespaceClient, $object)->create($object);

        // In order to migrate to deployments, also delete the existing replication controller
        $replicationControllerRepository = $namespaceClient->getReplicationControllerRepository();
        $replicationControllers = $replicationControllerRepository->findByLabels($deployment->getMetadata()->getLabelsAsAssociativeArray());
        foreach ($replicationControllers as $replicationController) {
            $replicationControllerRepository->delete($replicationController);
        }

        return $deployment;
    }
}
