<?php

namespace ContinuousPipe\Pipe\Kubernetes\Transformer;

use ContinuousPipe\Pipe\Kubernetes\Event\Transformation\ServiceTransformation;
use ContinuousPipe\Pipe\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServicePort;
use Kubernetes\Client\Model\ServiceSpecification;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ServiceTransformer
{
    /**
     * @var NamingStrategy
     */
    private $namingStrategy;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param NamingStrategy           $namingStrategy
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(NamingStrategy $namingStrategy, EventDispatcherInterface $eventDispatcher)
    {
        $this->namingStrategy = $namingStrategy;
        $this->eventDispatcher = $eventDispatcher;
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

        $event = new ServiceTransformation($component, $service);
        $this->eventDispatcher->dispatch(ServiceTransformation::POST_SERVICE_TRANSFORMATION, $event);

        return $event->getService();
    }
}
