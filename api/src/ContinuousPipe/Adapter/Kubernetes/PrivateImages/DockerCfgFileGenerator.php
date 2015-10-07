<?php

namespace ContinuousPipe\Adapter\Kubernetes\PrivateImages;

use ContinuousPipe\User\DockerRegistryCredentials;

class DockerCfgFileGenerator
{
    /**
     * @param DockerRegistryCredentials[] $credentials
     *
     * @return string
     */
    public function generate(array $credentials)
    {
        $config = [];
        foreach ($credentials as $credential) {
            $serverAddress = $this->getServerAddress($credential);
            $config[$serverAddress] = [
                'auth' => base64_encode(sprintf(
                    '%s:%s',
                    $credential->getUsername(),
                    $credential->getPassword()
                )),
                'email' => $credential->getEmail(),
            ];
        }

        return json_encode($config);
    }

    /**
     * Get the server address for that docker registry credentials.
     *
     * @param DockerRegistryCredentials $credentials
     *
     * @return string
     */
    private function getServerAddress(DockerRegistryCredentials $credentials)
    {
        $serverAddress = $credentials->getServerAddress();
        if ('docker.io' == $serverAddress) {
            $serverAddress = 'https://index.docker.io/v1/';
        }

        return $serverAddress;
    }
}
