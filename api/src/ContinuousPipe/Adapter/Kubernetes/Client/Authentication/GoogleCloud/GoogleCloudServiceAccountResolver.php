<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client\Authentication\GoogleCloud;

use ContinuousPipe\Adapter\Kubernetes\Client\ClientException;
use Google\Auth\Credentials\ServiceAccountCredentials;

class GoogleCloudServiceAccountResolver
{
    /**
     * @param string $serviceAccountAsString
     *
     * @throws ClientException
     *
     * @return string
     */
    public function token(string $serviceAccountAsString) : string
    {
        try {
            $serviceAccount = \GuzzleHttp\json_decode(base64_decode($serviceAccountAsString), true);
        } catch (\InvalidArgumentException $e) {
            throw new ClientException('Service account is not a valid JSON: '.$e->getMessage(), $e->getCode(), $e);
        }

        $credentials = new ServiceAccountCredentials('https://www.googleapis.com/auth/cloud-platform', $serviceAccount);
        try {
            $token = $credentials->fetchAuthToken();
        } catch (\RuntimeException $e) {
            throw new ClientException('Can\'t get token from Google Cloud: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (!isset($token['access_token'])) {
            throw new ClientException('Access token could not be found in Google Auth response');
        }

        return $token['access_token'];
    }
}
