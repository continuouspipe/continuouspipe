<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

use ContinuousPipe\Adapter\Kubernetes\PublicEndpoint\EndpointFactory;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;

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
     * @var DeploymentFactory
     */
    private $deploymentFactory;

    /**
     * @param PodTransformer               $podTransformer
     * @param ServiceTransformer           $serviceTransformer
     * @param ReplicationControllerFactory $replicationControllerFactory
     * @param EndpointFactory              $endpointFactory
     * @param DeploymentFactory            $deploymentFactory
     */
    public function __construct(PodTransformer $podTransformer, ServiceTransformer $serviceTransformer, ReplicationControllerFactory $replicationControllerFactory, EndpointFactory $endpointFactory, DeploymentFactory $deploymentFactory)
    {
        $this->podTransformer = $podTransformer;
        $this->serviceTransformer = $serviceTransformer;
        $this->replicationControllerFactory = $replicationControllerFactory;
        $this->endpointFactory = $endpointFactory;
        $this->deploymentFactory = $deploymentFactory;
    }

    /**
     * @param Component  $component
     * @param Kubernetes $cluster
     *
     * @return \Kubernetes\Client\Model\KubernetesObject[]
     */
    public function getElementListFromComponent(Component $component, Kubernetes $cluster = null)
    {
        $objects = [];
        $pod = $this->podTransformer->getPodFromComponent($component);

        if ($this->needsAService($component)) {
            $objects[] = $this->serviceTransformer->getServiceFromComponent($component);
        }

        foreach ($component->getEndpoints() as $endpoint) {
            $objects = array_merge($objects, $this->endpointFactory->createObjectsFromEndpoint($component, $endpoint));
        }

        if (!$this->isScalable($component)) {
            $objects[] = $pod;
        } elseif ($this->supportsDeployment($cluster)) {
            $objects[] = $this->deploymentFactory->createFromComponentPod($component, $pod);
        } else {
            $objects[] = $this->replicationControllerFactory->createFromComponentPod($component, $pod);
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
    private function isScalable(Component $component)
    {
        $scalabilityConfiguration = $component->getSpecification()->getScalability();

        return $scalabilityConfiguration->isEnabled();
    }

    /**
     * @param Kubernetes $cluster
     *
     * @return bool
     */
    private function supportsDeployment(Kubernetes $cluster = null)
    {
        if (null === $cluster) {
            return false;
        }

        $version = $cluster->getVersion();
        if (substr($version, 0, 1) == 'v') {
            $version = substr($version, 1);
        }

        return version_compare($version, '1.2') >= 0;
    }
}
