<?php

namespace ContinuousPipe\Builder;

class Repository
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $branch;

    /**
     * @param string $address
     * @param string $branch
     */
    public function __construct($address, $branch)
    {
        $this->address = $address;
        $this->branch = $branch;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }
}
