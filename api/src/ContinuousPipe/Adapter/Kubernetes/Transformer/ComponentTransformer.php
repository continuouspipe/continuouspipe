<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

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
     * @param PodTransformer $podTransformer
     * @param ServiceTransformer $serviceTransformer
     * @param ReplicationControllerFactory $replicationControllerFactory
     */
    public function __construct(PodTransformer $podTransformer, ServiceTransformer $serviceTransformer, ReplicationControllerFactory $replicationControllerFactory)
    {
        $this->podTransformer = $podTransformer;
        $this->serviceTransformer = $serviceTransformer;
        $this->replicationControllerFactory = $replicationControllerFactory;
    }

    /**
     * @param Component $component
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

        if ($this->needsAReplicationController($component)) {
            $objects[] = $this->replicationControllerFactory->createFromComponentPod($component, $pod);
        } else {
            $objects[] = $pod;
        }

        return $objects;
    }

    /**
     * @param Component $component
     *
     * @return bool
     */
    private function needsAService(Component $component)
    {
        $specification = $component->getSpecification();

        return $specification->getAccessibility()->isFromCluster();
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
