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
                $replicas,
                null,
                $this->getDeploymentStrategy($component)
            )
        );
    }

    /**
     * @param Component $component
     *
     * @return Deployment\DeploymentStrategy
     */
    private function getDeploymentStrategy(Component $component)
    {
        $maxUnavailable = $component->getDeploymentStrategy()->getMaxUnavailable();
        $maxSurge = $component->getDeploymentStrategy()->getMaxSurge();

        if (null === $maxUnavailable) {
            $volumes = $component->getSpecification()->getVolumes();

            $maxUnavailable = count($volumes) == 0 ? 0 : 1;
        }

        return new Deployment\DeploymentStrategy(
            Deployment\DeploymentStrategy::TYPE_ROLLING_UPDATE,
            new Deployment\RollingUpdateDeployment(
                $maxUnavailable,
                $maxSurge
            )
        );
    }
}
