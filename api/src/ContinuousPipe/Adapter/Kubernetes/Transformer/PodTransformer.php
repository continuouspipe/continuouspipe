<?php

namespace ContinuousPipe\Adapter\Kubernetes\Transformer;

use ContinuousPipe\Adapter\Kubernetes\Naming\NamingStrategy;
use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Container;
use Kubernetes\Client\Model\ContainerPort;
use Kubernetes\Client\Model\EnvironmentVariable;
use Kubernetes\Client\Model\Pod;
use Kubernetes\Client\Model\PodSpecification;
use Kubernetes\Client\Model\VolumeMount;

class PodTransformer
{
    /**
     * @var NamingStrategy
     */
    private $namingStrategy;
    /**
     * @var VolumeTransformer
     */
    private $volumeTransformer;

    public function __construct(NamingStrategy $namingStrategy, VolumeTransformer $volumeTransformer)
    {
        $this->namingStrategy = $namingStrategy;
        $this->volumeTransformer = $volumeTransformer;
    }

    /**
     * @param Component $component
     *
     * @return Pod
     */
    public function getPodFromComponent(Component $component)
    {
        $specification = new PodSpecification(
            [
                $this->createContainer($component->getIdentifier(), $component->getSpecification()),
            ],
            $this->createVolumes($component->getSpecification()),
            $this->getPodRestartPolicy($component),
            PodSpecification::DNS_POLICY_CLUSTER_FIRST
        );

        $metadata = $this->namingStrategy->getObjectMetadataFromComponent($component);

        return new Pod($metadata, $specification);
    }

    /**
     * @param Component\Specification $specification
     *
     * @return array
     */
    private function createVolumes(Component\Specification $specification)
    {
        $volumes = [];

        foreach ($specification->getVolumes() as $componentVolume) {
            $volumes[] = $this->volumeTransformer->getVolumeFromComponentVolume($componentVolume);
        }

        return $volumes;
    }

    /**
     * @param string                  $name
     * @param Component\Specification $specification
     *
     * @return Container
     */
    private function createContainer($name, Component\Specification $specification)
    {
        return new Container(
            $name,
            $this->getImageName($specification->getSource()),
            $this->createEnvironmentVariables($specification->getEnvironmentVariables()),
            $this->createPorts($specification->getPorts()),
            $this->createVolumeMounts($specification->getVolumeMounts()),
            Container::PULL_POLICY_ALWAYS,
            $specification->getCommand()
        );
    }

    /**
     * @param Component\VolumeMount[] $volumeMounts
     *
     * @return VolumeMount[]
     */
    private function createVolumeMounts(array $volumeMounts)
    {
        return array_map(function (Component\VolumeMount $volumeMount) {
            return new VolumeMount($volumeMount->getName(), $volumeMount->getMountPath(), $volumeMount->isReadOnly());
        }, $volumeMounts);
    }

    /**
     * @param Component\Port[] $ports
     *
     * @return ContainerPort[]
     */
    private function createPorts(array $ports)
    {
        return array_map(function (Component\Port $port) {
            return new ContainerPort($port->getIdentifier(), $port->getPort(), strtoupper($port->getProtocol()));
        }, $ports);
    }

    /**
     * @param Component\EnvironmentVariable[] $environmentVariables
     *
     * @return EnvironmentVariable[]
     */
    private function createEnvironmentVariables(array $environmentVariables)
    {
        return array_map(function (Component\EnvironmentVariable $environmentVariable) {
            return new EnvironmentVariable($environmentVariable->getName(), $environmentVariable->getValue());
        }, $environmentVariables);
    }

    /**
     * @param Component\Source $source
     *
     * @return string
     */
    private function getImageName(Component\Source $source)
    {
        $image = $source->getImage();

        if ($repository = $source->getRepository()) {
            $image = $repository.'/'.$image;
        }
        if ($tag = $source->getTag()) {
            $image = $image.':'.$tag;
        }

        return $image;
    }

    /**
     * Get pod's restart policy.
     *
     * @param Component $component
     *
     * @return string
     */
    private function getPodRestartPolicy(Component $component)
    {
        $scalability = $component->getSpecification()->getScalability();

        return $scalability->isEnabled() ? PodSpecification::RESTART_POLICY_ALWAYS : PodSpecification::RESTART_POLICY_NEVER;
    }
}
