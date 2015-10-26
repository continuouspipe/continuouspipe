<?php

namespace ContinuousPipe\River\Task\Deploy\ExposedContext;

class ServiceList
{
    /**
     * @var array
     */
    private $services;

    /**
     * @param array $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $key
     *
     * @return object
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->services)) {
            throw new \InvalidArgumentException(sprintf('The service "%s" do not exists', $key));
        }

        return $this->services[$key];
    }
}
