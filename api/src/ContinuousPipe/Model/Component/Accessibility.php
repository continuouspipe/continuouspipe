<?php

namespace ContinuousPipe\Model\Component;

class Accessibility
{
    /**
     * @var bool
     */
    private $fromCluster;

    /**
     * @var bool
     */
    private $fromExternal = false;

    /**
     * @param bool $fromCluster
     * @param bool $fromExternal
     */
    public function __construct($fromCluster, $fromExternal = false)
    {
        $this->fromCluster = $fromCluster;
        $this->fromExternal = $fromExternal;
    }

    /**
     * @return bool
     */
    public function isFromCluster()
    {
        return $this->fromCluster;
    }

    /**
     * @return bool
     */
    public function isFromExternal()
    {
        return $this->fromExternal;
    }
}
