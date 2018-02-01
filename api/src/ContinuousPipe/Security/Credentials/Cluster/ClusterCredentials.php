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
     * The certificate most be in the PKCS 12 format. In order to transform a certificate + private key, you can use
     * openssl this way:
     *
     * $ openssl pkcs12 -export -in [CERT-PATH] -inkey [KEY-PATH] -passout pass:[PASSWORD]
     *
     * @var string|null
     */
    private $clientCertificate;

    /**
     * Password for the client certificate, if needed.
     *
     * @var string|null
     */
    private $clientCertificatePassword;

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
        string $clientCertificatePassword = null,
        string $googleCloudServiceAccount = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->clientCertificate = $clientCertificate;
        $this->clientCertificatePassword = $clientCertificatePassword;
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
    public function getClientCertificatePassword()
    {
        return $this->clientCertificatePassword;
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
