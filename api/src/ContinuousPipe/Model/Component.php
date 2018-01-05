<?php

namespace ContinuousPipe\Model;

use ContinuousPipe\Model\Component\DeploymentStrategy;
use ContinuousPipe\Model\Component\Endpoint;
use ContinuousPipe\Model\Component\Specification;
use ContinuousPipe\Model\Component\Status;

class Component
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var Specification|null
     */
    private $specification;

    /**
     * @var Extension[]
     */
    private $extensions;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var string[]
     */
    private $labels;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var DeploymentStrategy
     */
    private $deploymentStrategy;

    /**
     * @var Endpoint[]
     */
    private $endpoints;

    /**
     * @param string             $identifier
     * @param string             $name
     * @param Specification|null $specification
     * @param Extension[]        $extensions
     * @param array              $labels
     * @param Status             $status
     * @param DeploymentStrategy $deploymentStrategy
     * @param Endpoint[]         $endpoints
     */
    public function __construct($identifier, $name, Specification $specification = null, array $extensions = [], array $labels = [], Status $status = null, DeploymentStrategy $deploymentStrategy = null, array $endpoints = [])
    {
        $this->name = $name;
        $this->identifier = $identifier;
        $this->specification = $specification;
        $this->extensions = $extensions;
        $this->labels = $labels;
        $this->status = $status;
        $this->deploymentStrategy = $deploymentStrategy;
        $this->endpoints = $endpoints;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return Specification|null
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @param Specification $specification
     */
    public function setSpecification(Specification $specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return \ContinuousPipe\Model\Extension[]
     */
    public function getExtensions()
    {
        return $this->extensions ?: [];
    }

    /**
     * @param \ContinuousPipe\Model\Extension[] $extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @param string $name
     *
     * @return \ContinuousPipe\Model\Extension|null
     */
    public function getExtension($name)
    {
        foreach ($this->getExtensions() as $extension) {
            if ($extension->getName() === $name) {
                return $extension;
            }
        }

        return;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string[]
     */
    public function getLabels()
    {
        return $this->labels ?: [];
    }

    /**
     * @deprecated
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->deploymentStrategy->isLocked();
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return DeploymentStrategy
     */
    public function getDeploymentStrategy()
    {
        return $this->deploymentStrategy ?: new DeploymentStrategy();
    }

    /**
     * @return Component\Endpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints ?: [];
    }

    /**
     * @param Endpoint[] $endpoints
     */
    public function setEndpoints(array $endpoints)
    {
        $this->endpoints = $endpoints;
    }
}
