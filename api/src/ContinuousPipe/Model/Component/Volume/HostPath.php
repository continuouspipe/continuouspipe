<?php

namespace ContinuousPipe\Model\Component\Volume;

use ContinuousPipe\Model\Component\Volume;

class HostPath extends Volume
{
    const TYPE = 'hostPath';

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $name
     * @param string $path
     */
    public function __construct($name, $path)
    {
        parent::__construct($name);

        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
