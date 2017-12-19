<?php

namespace ContinuousPipe\Security\Encryption\GoogleKms;

class GoogleKmsClientResolver
{
    /**
     * @var string
     */
    private $serviceAccountPath;

    /**
     * @var \Google_Service_CloudKMS|null
     */
    private $client;

    /**
     * @param string $serviceAccountPath
     */
    public function __construct(string $serviceAccountPath)
    {
        $this->serviceAccountPath = $serviceAccountPath;
    }

    public function get(\Google_Client $googleClient = null) : \Google_Service_CloudKMS
    {
        if (null === $this->client) {
            $googleClient = $googleClient ?: new \Google_Client();
            $googleClient->setAuthConfig($this->serviceAccountPath);
            $googleClient->setScopes(array(
                'https://www.googleapis.com/auth/cloud-platform'
            ));

            // Instantiate the Key Management Service API
            $this->client = new \Google_Service_CloudKMS($googleClient);
        }

        return $this->client;
    }
}
