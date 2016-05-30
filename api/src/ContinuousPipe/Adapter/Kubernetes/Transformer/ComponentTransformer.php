<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointFactory;
use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\KubernetesObject;

class ComponentTransformer
{
    /**
     * @var PodTransformer
     */
    private $podTransformer;
    /**
     * @var ServiceTransformer
     */
    private $serviceTransformer;
    /**
     * @var ReplicationControllerFactory
     */
    private $replicationControllerFactory;
    /**
     * @var EndpointFactory
     */
    private $endpointFactory;

    /**
     * @param PodTransformer               $podTransformer
     * @param ServiceTransformer           $serviceTransformer
     * @param ReplicationControllerFactory $replicationControllerFactory
     * @param EndpointFactory              $endpointFactory
     */
    public function __construct(PodTransformer $podTransformer, ServiceTransformer $serviceTransformer, ReplicationControllerFactory $replicationControllerFactory, EndpointFactory $endpointFactory)
    {
        $this->podTransformer = $podTransformer;
        $this->serviceTransformer = $serviceTransformer;
        $this->replicationControllerFactory = $replicationControllerFactory;
        $this->endpointFactory = $endpointFactory;
    }

    /**
     * @param Component $component
     *
     * @throws TransformationException
     *
     * @return KubernetesObject[]
     */
    public function getElementListFromComponent(Component $component)
    {
        $objects = [];
        $pod = $this->podTransformer->getPodFromComponent($component);

        if ($this->needsAService($component)) {
            $objects[] = $this->serviceTransformer->getServiceFromComponent($component);
        }

        foreach ($component->getEndpoints() as $endpoint) {
            $objects = array_merge($objects, $this->endpointFactory->createObjectsFromEndpoint($component, $endpoint));
        }

        if ($this->needsAReplicationController($component)) {
            $objects[] = $this->replicationControllerFactory->createFromComponentPod($component, $pod);
        } else {
            $objects[] = $pod;
        }

        return $objects;
    }

    /**
     * @deprecated We should only use endpoints
     *
     * @param Component $component
     *
     * @return bool
     */
    private function needsAService(Component $component)
    {
        $specification = $component->getSpecification();

        if (null !== ($accessibility = $specification->getAccessibility())) {
            return $accessibility->isFromCluster();
        }

        return false;
    }

    /**
     * @param Component $component
     *
     * @return bool
     */
    private function needsAReplicationController(Component $component)
    {
        $scalabilityConfiguration = $component->getSpecification()->getScalability();

        return $scalabilityConfiguration->isEnabled();
    }
}
