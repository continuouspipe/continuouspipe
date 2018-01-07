<?php

namespace ContinuousPipe\Model\Component;

class ResourcesRequest
{
    /**
     * @var string
     */
    private $cpu;

    /**
     * @var string
     */
    private $memory;

    /**
     * @param string $cpu
     * @param string $memory
     */
    public function __construct($cpu = null, $memory = null)
    {
        $this->cpu = $cpu;
        $this->memory = $memory;
    }

    /**
     * @return string
     */
    public function getCpu()
    {
        return $this->cpu;
    }

    /**
     * @return string
     */
    public function getMemory()
    {
        return $this->memory;
    }
}
