<?php

namespace ContinuousPipe\Model\Component\Endpoint;

class CloudFlareAuthentication
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $email
     * @param string $apiKey
     */
    public function __construct(string $email, string $apiKey)
    {
        $this->email = $email;
        $this->apiKey = $apiKey;
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
