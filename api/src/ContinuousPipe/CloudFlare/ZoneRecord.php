<?php

namespace ContinuousPipe\CloudFlare;

class ZoneRecord
{
    /**
     * @var string
     */
    private $hostname;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $address;

    /**
     * @param string $hostname
     * @param string $type
     * @param string $address
     */
    public function __construct(string $hostname, string $type, string $address)
    {
        $this->hostname = $hostname;
        $this->type = $type;
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }
}
