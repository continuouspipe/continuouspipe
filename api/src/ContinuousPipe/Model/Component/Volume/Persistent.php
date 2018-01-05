<?php

namespace ContinuousPipe\Model\Component\Volume;

use ContinuousPipe\Model\Component\Volume;

class Persistent extends Volume
{
    const TYPE = 'persistent';

    /**
     * @var string
     */
    private $capacity;

    /**
     * @var string
     */
    private $storageClass;

    /**
     * @param string $name
     * @param string $capacity
     * @param string $storageClass
     */
    public function __construct($name, $capacity, $storageClass = null)
    {
        parent::__construct($name);

        $this->capacity = $capacity;
        $this->storageClass = $storageClass;
    }

    /**
     * @return string
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @return string
     */
    public function getStorageClass()
    {
        return $this->storageClass;
    }
}
