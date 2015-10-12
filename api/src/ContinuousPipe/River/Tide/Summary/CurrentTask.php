<?php

namespace ContinuousPipe\River\Tide\Summary;

class CurrentTask
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $log;

    /**
     * @param string $name
     * @param string $log
     */
    public function __construct($name, $log = null)
    {
        $this->name = $name;
        $this->log = $log;
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
    public function getLog()
    {
        return $this->log;
    }
}
