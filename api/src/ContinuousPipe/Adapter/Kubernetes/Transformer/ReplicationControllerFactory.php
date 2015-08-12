<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodTemplateSpecification;
use Kubernetes\Client\Model\ReplicationController;
use Kubernetes\Client\Model\ReplicationControllerSpecification;

class ReplicationControllerFactory
{
    /**
     * @param Component $component
     * @param Pod       $pod
     *
     * @return ReplicationController
     */
    public function createFromComponentPod(Component $component, Pod $pod)
    {
        $scalabilityConfiguration = $component->getSpecification()->getScalability();

        $podTemplateSpecification = new PodTemplateSpecification($pod->getMetadata(), $pod->getSpecification());
        $selector = $pod->getMetadata()->getLabelsAsAssociativeArray();
        $specification = new ReplicationControllerSpecification($scalabilityConfiguration->getNumberOfReplicas(), $selector, $podTemplateSpecification);
        $replicationController = new ReplicationController($pod->getMetadata(), $specification);

        return $replicationController;
    }
}
