<?php

namespace ContinuousPipe\Builder;

use ContinuousPipe\Security\Credentials\DockerRegistry;

class RegistryCredentials
{
    /**
     * @var string
     */
    private $authenticationString;

    /**
     * @param string $authenticationString
     *
     * @return RegistryCredentials
     */
    public static function fromAuthenticationString($authenticationString)
    {
        $credentials = new self();
        $credentials->authenticationString = $authenticationString;

        return $credentials;
    }

    /**
     * @param DockerRegistry $dockerRegistryCredentials
     *
     * @return RegistryCredentials
     */
    public static function fromDockerRegistryCredentials(DockerRegistry $dockerRegistryCredentials)
    {
        return self::fromAuthenticationString(base64_encode(json_encode([
            'username' => $dockerRegistryCredentials->getUsername(),
            'password' => $dockerRegistryCredentials->getPassword(),
            'email' => $dockerRegistryCredentials->getEmail(),
            'serveraddress' => $dockerRegistryCredentials->getServerAddress(),
        ])));
    }

    /**
     * @return string
     */
    public function getAuthenticationString()
    {
        return $this->authenticationString;
    }
}
