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
     * @var string
     */
    private $token;

    /**
     * @param string $address
     * @param string $branch
     * @param string $token
     */
    public function __construct($address, $branch, $token)
    {
        $this->address = $address;
        $this->branch = $branch;
        $this->token = $token;
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

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
