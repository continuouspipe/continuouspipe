<?php

namespace ContinuousPipe\Google\ContainerEngineCluster;

use JMS\Serializer\Annotation as JMS;

final class MasterAuthentication
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("username")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("password")
     *
     * @var string
     */
    private $password;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clusterCaCertificate")
     *
     * @var string
     */
    private $clusterCaCertificate;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clientCertificate")
     *
     * @var string
     */
    private $clientCertificate;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("clientKey")
     *
     * @var string
     */
    private $clientKey;

    public function __construct(string $username, string $password, string $clusterCaCertificate, string $clientCertificate, string $clientKey)
    {
        $this->username = $username;
        $this->password = $password;
        $this->clusterCaCertificate = $clusterCaCertificate;
        $this->clientCertificate = $clientCertificate;
        $this->clientKey = $clientKey;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getClusterCaCertificate(): string
    {
        return $this->clusterCaCertificate;
    }

    /**
     * @return string
     */
    public function getClientCertificate(): string
    {
        return $this->clientCertificate;
    }

    /**
     * @return string
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }
}
