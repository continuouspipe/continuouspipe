<?php

namespace ContinuousPipe\Pipe\Environment;

class PublicEndpoint
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @param string $name
     * @param string $address
     */
    public function __construct($name, $address)
    {
        $this->name = $name;
        $this->address = $address;
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
    public function getAddress()
    {
        return $this->address;
    }
}
