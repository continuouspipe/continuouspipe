<?php

namespace ContinuousPipe\Model\Component;

class Specification
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var Accessibility
     */
    private $accessibility;

    /**
     * @var Scalability
     */
    private $scalability;

    /**
     * @var Port[]
     */
    private $ports = [];

    /**
     * @var EnvironmentVariable[]
     */
    private $environmentVariables = [];

    /**
     * @var Volume[]
     */
    private $volumes;

    /**
     * @var VolumeMount[]
     */
    private $volumeMounts;

    /**
     * @var RuntimePolicy
     */
    private $runtimePolicy;

    /**
     * @var Resources|null
     */
    private $resources;

    /**
     * @var array
     */
    private $command;

    /**
     * @param Source                $source
     * @param Accessibility         $accessibility
     * @param Scalability           $scalability
     * @param Port[]                $ports
     * @param EnvironmentVariable[] $environmentVariables
     * @param Volume[]              $volumes
     * @param VolumeMount[]         $volumeMounts
     * @param array                 $command
     * @param RuntimePolicy|null    $runtimePolicy
     * @param Resources|null        $resources
     */
    public function __construct(Source $source, Accessibility $accessibility, Scalability $scalability, array $ports = [], array $environmentVariables = [], array $volumes = [], array $volumeMounts = [], array $command = null, RuntimePolicy $runtimePolicy = null, Resources $resources = null)
    {
        $this->source = $source;
        $this->accessibility = $accessibility;
        $this->scalability = $scalability;
        $this->ports = $ports;
        $this->environmentVariables = $environmentVariables;
        $this->volumes = $volumes;
        $this->volumeMounts = $volumeMounts;
        $this->command = $command;
        $this->runtimePolicy = $runtimePolicy;
        $this->resources = $resources;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @deprecated We should use `Component::endpoints` from now
     *
     * @return Accessibility
     */
    public function getAccessibility()
    {
        return $this->accessibility;
    }

    /**
     * @param Accessibility $accessibility
     */
    public function setAccessibility(Accessibility $accessibility)
    {
        $this->accessibility = $accessibility;
    }

    /**
     * @return Scalability
     */
    public function getScalability()
    {
        return $this->scalability ?: new Scalability(true);
    }

    /**
     * @return Port[]
     */
    public function getPorts()
    {
        return $this->ports ?: [];
    }

    /**
     * @deprecated Should use `getPorts`
     *
     * @return Port[]
     */
    public function getPortMappings()
    {
        return $this->getPorts();
    }

    /**
     * @return EnvironmentVariable[]
     */
    public function getEnvironmentVariables()
    {
        return $this->environmentVariables ?: [];
    }

    /**
     * @return Volume[]
     */
    public function getVolumes()
    {
        return $this->volumes ?: [];
    }

    /**
     * @return VolumeMount[]
     */
    public function getVolumeMounts()
    {
        return $this->volumeMounts ?: [];
    }

    /**
     * @param VolumeMount[] $volumeMounts
     */
    public function setVolumeMounts($volumeMounts)
    {
        $this->volumeMounts = $volumeMounts;
    }

    /**
     * @param EnvironmentVariable[] $environmentVariables
     */
    public function setEnvironmentVariables(array $environmentVariables)
    {
        $this->environmentVariables = $environmentVariables;
    }

    /**
     * @return array
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return RuntimePolicy|null
     */
    public function getRuntimePolicy()
    {
        return $this->runtimePolicy;
    }

    /**
     * @param RuntimePolicy $runtimePolicy
     */
    public function setRuntimePolicy(RuntimePolicy $runtimePolicy)
    {
        $this->runtimePolicy = $runtimePolicy;
    }

    /**
     * @return Resources|null
     */
    public function getResources()
    {
        if (null !== $this->resources && null === $this->resources->getLimits() && null === $this->resources->getRequests()) {
            return null;
        }

        return $this->resources;
    }

    /**
     * @param Resources|null $resources
     */
    public function setResources(Resources $resources = null)
    {
        $this->resources = $resources;
    }
}
