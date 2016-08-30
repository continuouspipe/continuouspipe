<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Deployment;
use Kubernetes\Client\Model\DeploymentSpecification;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodTemplateSpecification;

class DeploymentFactory
{
    /**
     * @param Component $component
     * @param Pod       $pod
     *
     * @return Deployment
     */
    public function createFromComponentPod(Component $component, Pod $pod)
    {
        $replicas = $component->getSpecification()->getScalability()->getNumberOfReplicas();

        return new Deployment(
            $pod->getMetadata(),
            new DeploymentSpecification(
                new PodTemplateSpecification(
                    $pod->getMetadata(),
                    $pod->getSpecification()
                ),
                $replicas
            )
        );
    }
}
