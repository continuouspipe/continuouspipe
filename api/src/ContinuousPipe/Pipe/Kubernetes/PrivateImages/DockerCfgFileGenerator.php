<?php

namespace ContinuousPipe\Pipe\Kubernetes\PrivateImages;

use ContinuousPipe\Security\Credentials\Bucket;
use ContinuousPipe\Security\Credentials\DockerRegistry;

class DockerCfgFileGenerator
{
    /**
     * @param Bucket $bucket
     *
     * @return string
     */
    public function generate(Bucket $bucket)
    {
        $config = [];
        foreach ($bucket->getDockerRegistries() as $credential) {
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
     * @param DockerRegistry $credentials
     *
     * @return string
     */
    private function getServerAddress(DockerRegistry $credentials)
    {
        $serverAddress = $credentials->getServerAddress();
        if ('docker.io' == $serverAddress) {
            $serverAddress = 'https://index.docker.io/v1/';
        }

        return $serverAddress;
    }
}
