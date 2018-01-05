<?php

namespace ContinuousPipe\Model\Component;

class RuntimePolicy
{
    /**
     * @var bool
     */
    private $privileged;

    /**
     * @param bool $privileged
     */
    public function __construct($privileged)
    {
        $this->privileged = $privileged;
    }

    /**
     * @return bool
     */
    public function isPrivileged()
    {
        return $this->privileged;
    }
}
