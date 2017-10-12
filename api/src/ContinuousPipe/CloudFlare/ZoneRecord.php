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
     * @var int
     */
    private $ttl;

    /**
     * @var bool
     */
    private $proxied;

    /**
     * @param string $hostname
     * @param string $type
     * @param string $address
     * @param int $ttl
     * @param bool $proxied
     */
    public function __construct(string $hostname, string $type, string $address, int $ttl = null, bool $proxied = null)
    {
        $this->hostname = $hostname;
        $this->type = $type;
        $this->address = $address;
        $this->ttl = $ttl;
        $this->proxied = $proxied;
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

    /**
     * @return int|null
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @return bool|null
     */
    public function isProxied()
    {
        return $this->proxied;
    }
}
