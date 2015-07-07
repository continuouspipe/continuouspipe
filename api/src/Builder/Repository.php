<?php

namespace Builder;

class Repository
{
    private $address;
    private $branch;

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
