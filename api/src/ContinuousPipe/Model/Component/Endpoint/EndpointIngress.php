<?php

namespace ContinuousPipe\Model\Component\Endpoint;

use Kubernetes\Client\Model\IngressRule;

class EndpointIngress
{
    /**
     * @var string|null
     */
    private $class;

    /**
     * @var IngressRule[]
     */
    private $rules;

    /**
     * @param string|null $class
     * @param IngressRule[] $rules
     */
    public function __construct($class = null, array $rules = [])
    {
        $this->class = $class;
        $this->rules = $rules;
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return IngressRule[]
     */
    public function getRules(): array
    {
        return $this->rules ?: [];
    }

    /**
     * @param null|string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param IngressRule[] $rules
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }
}
