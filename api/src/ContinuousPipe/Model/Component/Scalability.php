<?php

namespace ContinuousPipe\Model\Component;

class Scalability
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var int
     */
    private $numberOfReplicas;

    /**
     * @param bool $enabled
     * @param int  $numberOfReplicas
     */
    public function __construct($enabled, $numberOfReplicas = 1)
    {
        $this->enabled = $enabled;
        $this->numberOfReplicas = $numberOfReplicas;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return int
     */
    public function getNumberOfReplicas()
    {
        return $this->numberOfReplicas;
    }
}
