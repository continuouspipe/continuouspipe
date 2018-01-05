<?php

namespace ContinuousPipe\Model\Component\Status;

class Network
{
    private $address;

    private $hostAddress;

    public function __construct($address, $hostAddress)
    {
        $this->address = $address;
        $this->hostAddress = $hostAddress;
    }
}
