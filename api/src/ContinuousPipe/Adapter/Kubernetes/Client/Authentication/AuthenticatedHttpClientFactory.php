<?php

namespace ContinuousPipe\Adapter\Kubernetes\Client\Authentication;

use ContinuousPipe\Adapter\Kubernetes\Client\Authentication\GoogleCloud\GoogleCloudServiceAccountResolver;
use ContinuousPipe\Adapter\Kubernetes\Client\ClientException;
use ContinuousPipe\Security\Credentials\Cluster\ClusterCredentials;
use ContinuousPipe\Security\Credentials\Cluster\Kubernetes;
use Kubernetes\Client\Adapter\Http\AuthenticationMiddleware;
use Kubernetes\Client\Adapter\Http\HttpClient;

class AuthenticatedHttpClientFactory
{
    /**
     * @var GoogleCloudServiceAccountResolver
     */
    private $googleCloudServiceAccountResolver;

    /**
     * @param GoogleCloudServiceAccountResolver $googleCloudServiceAccountResolver
     */
    public function __construct(GoogleCloudServiceAccountResolver $googleCloudServiceAccountResolver)
    {
        $this->googleCloudServiceAccountResolver = $googleCloudServiceAccountResolver;
    }

    /**
     * @param HttpClient $client
     * @param Kubernetes $cluster
     *
     * @throws ClientException
     *
     * @return HttpClient
     */
    public function authenticatedClient(HttpClient $client, Kubernetes $cluster) : HttpClient
    {
        if (null !== ($credentials = $cluster->getManagementCredentials())) {
            return $this->authenticatedClientWithCredentials($client, $credentials);
        }

        return $this->authenticatedClientWithCredentials($client, $cluster->getCredentials());
    }

    private function authenticatedClientWithCredentials(HttpClient $httpClient, ClusterCredentials $credentials) : HttpClient
    {
        if (null !== $credentials->getGoogleCloudServiceAccount()) {
            return new AuthenticationMiddleware($httpClient, AuthenticationMiddleware::TOKEN, $this->googleCloudServiceAccountResolver->token($credentials->getGoogleCloudServiceAccount()));
        } elseif (null !== $credentials->getClientCertificate()) {
            return new AuthenticationMiddleware($httpClient, AuthenticationMiddleware::CERTIFICATE, $credentials->getClientCertificate());
        } elseif (null !== $credentials->getUsername()) {
            return new AuthenticationMiddleware($httpClient, AuthenticationMiddleware::USERNAME_PASSWORD, sprintf('%s:%s', $credentials->getUsername(), $credentials->getPassword()));
        }

        throw new ClientException('No authentication method found for this cluster');
    }
}
