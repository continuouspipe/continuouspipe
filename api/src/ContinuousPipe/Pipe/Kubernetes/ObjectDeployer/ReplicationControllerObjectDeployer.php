<?php

namespace ContinuousPipe\Pipe\Kubernetes\ObjectDeployer;

use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\NamespaceClient;

class ReplicationControllerObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    protected function update(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        /** @var ReplicationController $object */

        // Keeps the number of replicas of the RC
        if ($object->getSpecification()->getReplicas() <= 0) {
            $existingObject = $this->getRepository($namespaceClient, $object)->findOneByName(
                $object->getMetadata()->getName()
            );

            $object->getSpecification()->setReplicas(
                $existingObject->getSpecification()->getReplicas()
            );
        }

        $object = parent::update($namespaceClient, $object);

        // Has an extremely simple RC-update feature, we can delete matching RC's pods
        // Wait the "real" rolling-update feature
        // @link https://github.com/sroze/continuouspipe/issues/54
        $this->deleteReplicationControllerPods($namespaceClient, $object);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    protected function create(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        /** @var ReplicationController $object */
        if ($object->getSpecification()->getReplicas() <= 0) {
            $object->getSpecification()->setReplicas(1);
        }

        return parent::create($namespaceClient, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof ReplicationController;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getReplicationControllerRepository();
    }

    /**
     * @param NamespaceClient       $namespaceClient
     * @param ReplicationController $replicationController
     */
    private function deleteReplicationControllerPods(NamespaceClient $namespaceClient, ReplicationController $replicationController)
    {
        $podRepository = $namespaceClient->getPodRepository();
        $pods = $podRepository->findByReplicationController($replicationController);

        foreach ($pods as $pod) {
            $podRepository->delete($pod);
        }
    }
}
