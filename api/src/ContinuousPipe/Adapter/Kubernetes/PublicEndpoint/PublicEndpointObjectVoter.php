<?php

namespace ContinuousPipe\Adapter\Kubernetes\PublicEndpoint;

use Kubernetes\Client\Model\Ingress;
use Kubernetes\Client\Model\KubernetesObject;
use Kubernetes\Client\Model\Service;
use Kubernetes\Client\Model\ServiceSpecification;

class PublicEndpointObjectVoter
{
    /**
     * Return true if this is a public service.
     *
     * @param KubernetesObject $object
     *
     * @return bool
     */
    public function isPublicEndpointObject(KubernetesObject $object)
    {
        if ($object instanceof Ingress) {
            return true;
        } elseif ($object instanceof Service) {
            return $object->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER
                || $object->getMetadata()->getLabelList()->hasKey('source-of-ingress');
        }

        return false;
    }
}
