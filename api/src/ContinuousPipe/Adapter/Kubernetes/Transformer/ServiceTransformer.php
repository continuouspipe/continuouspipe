<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServicePort;
use Kubernetes\Client\Model\ServiceSpecification;

class ServiceTransformer
{
    /**
     * @var NamingStrategy
     */
    private $namingStrategy;

    /**
     * @param NamingStrategy $namingStrategy
     */
    public function __construct(NamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param Component $component
     *
     * @return Service
     */
    public function getServiceFromComponent(Component $component)
    {
        $ports = [];
        foreach ($component->getSpecification()->getPortMappings() as $port) {
            $ports[] = new ServicePort($port->getIdentifier(), $port->getPort(), $port->getProtocol());
        }

        $accessibilityConfiguration = $component->getSpecification()->getAccessibility();
        $type = ServiceSpecification::TYPE_CLUSTER_IP;
        if ($accessibilityConfiguration->isFromExternal()) {
            $type = ServiceSpecification::TYPE_LOAD_BALANCER;
        }

        $objectMetadata = $this->namingStrategy->getObjectMetadataFromComponent($component);
        $serviceSpecification = new ServiceSpecification($objectMetadata->getLabelsAsAssociativeArray(), $ports, $type);
        $service = new Service($objectMetadata, $serviceSpecification);

        return $service;
    }
}
