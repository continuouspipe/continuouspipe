<?php

namespace ContinuousPipe\Security\Credentials\Cluster;

use ContinuousPipe\Security\Credentials\Cluster;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Kubernetes extends Cluster
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string|null
     */
    private $caCertificate;

    /**
     * @var ClusterCredentials|null
     */
    private $managementCredentials;

    /**
     * @deprecated Kept for BC-purposes
     *
     * @var string|null
     */
    private $username;

    /**
     * @deprecated Kept for BC-purposes
     *
     * @var string|null
     */
    private $password;

    /**
     * @deprecated Kept for BC-purposes
     *
     * @var string|null
     */
    private $clientCertificate;

    /**
     * @deprecated Kept for BC-purposes
     *
     * @var null|string
     */
    private $googleCloudServiceAccount;

    /**
     * @param string $identifier
     * @param string $address
     * @param string $version
     * @param string|null $username
     * @param string|null $password
     * @param ClusterPolicy[] $policies
     * @param string|null $clientCertificate
     * @param string|null $caCertificate
     * @param string|null $googleCloudServiceAccount
     * @param ClusterCredentials|null $managementCredentials
     */
    public function __construct(
        $identifier,
        $address,
        $version,
        $username = null,
        $password = null,
        array $policies = [],
        string $clientCertificate = null,
        string $caCertificate = null,
        string $googleCloudServiceAccount = null,
        ClusterCredentials $managementCredentials = null
    ) {
        parent::__construct($identifier, $policies);

        $this->address = $address;
        $this->version = $version;
        $this->username = $username;
        $this->password = $password;
        $this->clientCertificate = $clientCertificate;
        $this->caCertificate = $caCertificate;
        $this->googleCloudServiceAccount = $googleCloudServiceAccount;
        $this->managementCredentials = $managementCredentials;
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
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getCaCertificate()
    {
        return $this->caCertificate;
    }

    /**
     * @return ClusterCredentials
     */
    public function getCredentials()
    {
        return new ClusterCredentials(
            $this->username,
            $this->password,
            $this->clientCertificate,
            $this->googleCloudServiceAccount
        );
    }

    /**
     * @return ClusterCredentials|null
     */
    public function getManagementCredentials()
    {
        if (null !== $this->managementCredentials && $this->managementCredentials->isEmpty()) {
            return null;
        }

        return $this->managementCredentials;
    }

    /**
     * @param ClusterCredentials|null $managementCredentials
     */
    public function setManagementCredentials(ClusterCredentials $managementCredentials = null)
    {
        $this->managementCredentials = $managementCredentials;
    }

    /**
     * @deprecated Kept for BC-purposes. Uses `getCredentials()`.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @deprecated Kept for BC-purposes. Uses `getCredentials()`.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @deprecated Kept for BC-purposes. Uses `getCredentials()`.
     *
     * @return null|string
     */
    public function getClientCertificate()
    {
        return $this->clientCertificate;
    }

    /**
     * @deprecated Kept for BC-purposes. Uses `getCredentials()`.
     *
     * @return null|string
     */
    public function getGoogleCloudServiceAccount()
    {
        return $this->googleCloudServiceAccount;
    }

    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->clientCertificate || null !== $this->googleCloudServiceAccount) {
            return;
        } elseif (null === $this->username) {
            $context->buildViolation('Username should not be blank')
                ->atPath('username')
                ->addViolation();
        } elseif (null === $this->password) {
            $context->buildViolation('Password should not be blank')
                ->atPath('password')
                ->addViolation();
        }
    }
}
