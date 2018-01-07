<?php


namespace ContinuousPipe\Security\Credentials\Cluster;

class ClusterCredentials
{
    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * Base64-encoded client certificate.
     *
     * @var string|null
     */
    private $clientCertificate;

    /**
     * Base64-encoding JSON Google Cloud Service account.
     *
     * @var null|string
     */
    private $googleCloudServiceAccount;

    public function __construct(
        string $username = null,
        string $password = null,
        string $clientCertificate = null,
        string $googleCloudServiceAccount = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->clientCertificate = $clientCertificate;
        $this->googleCloudServiceAccount = $googleCloudServiceAccount;
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return null|string
     */
    public function getClientCertificate()
    {
        return $this->clientCertificate;
    }

    /**
     * @return null|string
     */
    public function getGoogleCloudServiceAccount()
    {
        return $this->googleCloudServiceAccount;
    }

    /**
     * Return true if the credentials are empty.
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->username === null && $this->password === null && $this->clientCertificate === null && $this->googleCloudServiceAccount === null;
    }
}
