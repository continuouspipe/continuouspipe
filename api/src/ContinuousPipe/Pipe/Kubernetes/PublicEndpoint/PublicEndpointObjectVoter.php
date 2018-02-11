<?php

namespace ContinuousPipe\Pipe\Kubernetes\PublicEndpoint;

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
            return $this->isInternalEndpoint($object)
                || $object->getSpecification()->getType() == ServiceSpecification::TYPE_LOAD_BALANCER
                || (
                    $object->getSpecification()->getType() == ServiceSpecification::TYPE_NODE_PORT
                    && !$object->getMetadata()->getLabelList()->hasKey('source-of-ingress')
                );
        }

        return false;
    }

    private function isInternalEndpoint(Service $object): bool
    {
        return $object->getMetadata()->getLabelList()->hasKey('internal-endpoint');
    }
}
