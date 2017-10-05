<?php

namespace ContinuousPipe\Pipe\Kubernetes\ObjectDeployer;

use ContinuousPipe\Model\Component\DeploymentStrategy;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServicePort;
use Kubernetes\Client\NamespaceClient;

class ServiceObjectDeployer extends AbstractObjectDeployer
{
    /**
     * {@inheritdoc}
     */
    public function supports(KubernetesObject $object)
    {
        return $object instanceof Service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepository(NamespaceClient $namespaceClient, KubernetesObject $object)
    {
        return $namespaceClient->getServiceRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function needsToBeUpdated(NamespaceClient $namespaceClient, KubernetesObject $object, DeploymentStrategy $deploymentStrategy = null)
    {
        if (!parent::needsToBeUpdated($namespaceClient, $object, $deploymentStrategy)) {
            return false;
        }

        /* @var Service $object  */
        /* @var Service $existingService  */
        $existingService = $this->getRepository($namespaceClient, $object)->findOneByName($object->getMetadata()->getName());

        return
            // Updates if the selector changed
            $existingService->getSpecification()->getSelector() != $object->getSpecification()->getSelector()

            ||
            // Update it the type changed
            $existingService->getSpecification()->getType() != $object->getSpecification()->getType()

            ||
            // Update if the ports changed
            $this->portHash($existingService) != $this->portHash($object)
        ;
    }

    private function portHash(Service $service) : array
    {
        return array_map(function (ServicePort $port) {
            return [
                'name' => strtolower($port->getName()),
                'protocol' => strtolower($port->getProtocol()),
                'port' => (int) $port->getPort(),
                'target' => (int) $port->getTargetPort(),
            ];
        }, $service->getSpecification()->getPorts());
    }
}
