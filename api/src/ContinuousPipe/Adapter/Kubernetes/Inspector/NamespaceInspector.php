<?php

namespace ContinuousPipe\Adapter\Kubernetes\Inspector;

use ContinuousPipe\Model\Component;
use Kubernetes\Client\Exception\ServiceNotFound;
use Kubernetes\Client\Model\Container;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;
use Kubernetes\Client\NamespaceClient;

class NamespaceInspector
{
    /**
     * @param NamespaceClient $namespaceClient
     *
     * @return Component[]
     */
    public function getComponents(NamespaceClient $namespaceClient)
    {
        $components = [];
        $replicationControllers = $namespaceClient->getReplicationControllerRepository()->findAll()->getReplicationControllers();

        foreach ($replicationControllers as $replicationController) {
            $replicationControllerName = $replicationController->getMetadata()->getName();
            $containers = $replicationController->getSpecification()->getPodTemplateSpecification()->getPodSpecification()->getContainers();

            if (0 == count($containers)) {
                continue;
            }

            $container = $containers[0];
            $components[] = new Component($replicationControllerName, $replicationControllerName, new Component\Specification(
                $this->getComponentSource($container),
                $this->getComponentAccessibility($namespaceClient, $replicationController),
                new Component\Scalability(true, $replicationController->getSpecification()->getReplicas())
            ));
        }

        return $components;
    }

    /**
     * @param Container $container
     * @return Component\Source
     */
    private function getComponentSource(Container $container)
    {
        $imageName = $container->getImage();
        $tagName = null;

        if (($semiColonPosition = strpos($imageName, ':')) !== false) {
            $imageName = substr($imageName, 0, $semiColonPosition);
            $tagName = substr($imageName, $semiColonPosition);
        }

        return new Component\Source($imageName, $tagName);
    }

    /**
     * @param NamespaceClient $namespaceClient
     * @param ReplicationController $replicationController
     *
     * @return Component\Accessibility
     */
    private function getComponentAccessibility(NamespaceClient $namespaceClient, ReplicationController $replicationController)
    {
        try {
            $service = $namespaceClient->getServiceRepository()->findOneByName(
                $replicationController->getMetadata()->getName()
            );

            $externalService = $service->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER;

            return new Component\Accessibility(true, $externalService);
        } catch (ServiceNotFound $e) {
            return new Component\Accessibility(false, false);
        }
    }
}
