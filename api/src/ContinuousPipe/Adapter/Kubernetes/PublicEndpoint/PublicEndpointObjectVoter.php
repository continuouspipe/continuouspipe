<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;

class PublicEndpointObjectVoter
{
    public function isPublicEndpointObject(KubernetesObject $object) : bool
    {
        return
            $this->isThePrimaryPublicEndpointToWait($object)
            ||
            $object->getMetadata()->getLabelList()->hasKey('source-of-ingress')
        ;
    }

    public function isThePrimaryPublicEndpointToWait(KubernetesObject $object) : bool
    {
        if ($object instanceof Ingress) {
            return true;
        }

        if ($object instanceof Service) {
            return $this->isLoadBalancer($object) || $this->isInternalEndpoint($object);
        }

        return false;
    }

    /**
     * @param KubernetesObject $object
     * @return bool
     */
    private function isLoadBalancer(KubernetesObject $object)
    {
        return $object->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER;
    }

    /**
     * @param KubernetesObject $object
     * @return bool
     */
    private function isInternalEndpoint(KubernetesObject $object)
    {
        return $object->getMetadata()->getLabelList()->hasKey('internal-endpoint');
    }
}
