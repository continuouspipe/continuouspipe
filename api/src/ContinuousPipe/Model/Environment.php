<?php

namespace ContinuousPipe\Model;

class Environment
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Component[]
     */
    private $components = [];

    /**
     * @var Application
     */
    private $application;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var string
     */
    private $status;

    /**
     * @param string $identifier
     * @param string $name
     * @param Component[] $components
     * @param Application $application
     * @param array $labels
     * @param string $status
     */
    public function __construct($identifier, $name, array $components = [], Application $application = null, array $labels = [], $status = '')
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->components = $components;
        $this->application = $application;
        $this->labels = $labels;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Component[]
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param Component $component
     */
    public function addComponent(Component $component)
    {
        $this->components[] = $component;
    }

    /**
     * @return bool
     */
    public function hasComponents()
    {
        return count($this->components) > 0;
    }

    /**
     * Returns a component by its name.
     *
     * @param string $name
     *
     * @throws ComponentNotFound
     *
     * @return Component
     */
    public function getComponent($name)
    {
        $findComponent = function ($foundComponent, Component $component) use ($name) {
            return $component->getName() == $name ? $component : $foundComponent;
        };

        $foundComponent = array_reduce($this->components, $findComponent);

        if (!$foundComponent) {
            throw new ComponentNotFound(sprintf('Component named "%s" not found', $name));
        }

        return $foundComponent;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
