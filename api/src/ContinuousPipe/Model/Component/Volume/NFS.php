<?php

namespace ContinuousPipe\Model\Component\Volume;

use ContinuousPipe\Model\Component\Volume;

class NFS extends Volume
{
    const TYPE = 'nfs';

    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $readOnly;

    /**
     * @param string $name
     * @param string $server
     * @param string $path
     * @param bool   $readOnly
     */
    public function __construct($name, $server, $path, $readOnly = false)
    {
        parent::__construct($name);

        $this->server = $server;
        $this->path = $path;
        $this->readOnly = $readOnly;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }
}
