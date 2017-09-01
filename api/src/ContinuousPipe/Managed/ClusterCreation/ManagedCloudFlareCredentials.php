<?php

namespace ContinuousPipe\Managed\ClusterCreation;

final class ManagedCloudFlareCredentials
{
    /**
     * @var string
     */
    private $domainName;
    /**
     * @var string
     */
    private $zoneIdentifier;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $apiKey;

    private function __construct(string $domainName, string $zoneIdentifier, string $email, string $apiKey)
    {
        $this->domainName = $domainName;
        $this->zoneIdentifier = $zoneIdentifier;
        $this->email = $email;
        $this->apiKey = $apiKey;
    }

    public static function fromArray(array $array) : self
    {
        return new self(
            $array['domain-name'],
            $array['zone-identifier'],
            $array['email'],
            $array['api-key']
        );
    }

    /**
     * @return string
     */
    public function getDomainName(): string
    {
        return $this->domainName;
    }

    /**
     * @return string
     */
    public function getZoneIdentifier(): string
    {
        return $this->zoneIdentifier;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
