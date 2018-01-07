<?php

namespace ContinuousPipe\Model;

class Application
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
     * @var Environment[]
     */
    private $environments = [];

    /**
     * @param string        $identifier
     * @param string        $name
     * @param Environment[] $environments
     */
    public function __construct($identifier, $name, array $environments = [])
    {
        $this->name = $name;
        $this->identifier = $identifier;
        $this->environments = $environments;
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
     * @return Environment[]
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * @param Environment $environment
     */
    public function addEnvironment(Environment $environment)
    {
        $this->environments[] = $environment;
    }
}
