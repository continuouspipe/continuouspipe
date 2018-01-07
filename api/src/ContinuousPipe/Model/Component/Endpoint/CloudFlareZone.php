<?php

namespace ContinuousPipe\Model\Component\Endpoint;

class CloudFlareZone
{
    /**
     * @var string
     */
    private $zoneIdentifier;

    /**
     * @var CloudFlareAuthentication
     */
    private $authentication;

    /**
     * @var string|null
     */
    private $hostname;

    /**
     * @var string|null
     */
    private $recordSuffix;

    /**
     * Manually specify the backend address.
     *
     * @var string|null
     */
    private $backendAddress;

    /**
     * @var int|null
     */
    private $ttl;

    /**
     * @var bool|null
     */
    private $proxied;

    /**
     * @param string $zoneIdentifier
     * @param CloudFlareAuthentication $authentication
     * @param string $hostname
     * @param string $recordSuffix
     * @param int $ttl
     * @param bool $proxied
     * @param string $backendAddress
     */
    public function __construct(string $zoneIdentifier, CloudFlareAuthentication $authentication, string $hostname = null, string $recordSuffix = null, int $ttl = null, bool $proxied = null, string $backendAddress = null)
    {
        $this->zoneIdentifier = $zoneIdentifier;
        $this->authentication = $authentication;
        $this->hostname = $hostname;
        $this->recordSuffix = $recordSuffix;
        $this->ttl = $ttl;
        $this->proxied = $proxied;
        $this->backendAddress = $backendAddress;
    }

    /**
     * @return string
     */
    public function getZoneIdentifier(): string
    {
        return $this->zoneIdentifier;
    }

    /**
     * @return CloudFlareAuthentication
     */
    public function getAuthentication(): CloudFlareAuthentication
    {
        return $this->authentication;
    }

    /**
     * @return string|null
     */
    public function getRecordSuffix()
    {
        return $this->recordSuffix;
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

    /**
     * @return null|string
     */
    public function getBackendAddress()
    {
        return $this->backendAddress;
    }

    /**
     * @return null|string
     */
    public function getHostname()
    {
        return $this->hostname;
    }
}
