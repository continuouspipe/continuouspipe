<?php

namespace ContinuousPipe\River\GitHub;

use GitHub\Integration\Installation;
use Github\Client;
use Github\HttpClient\HttpClientInterface;
use GitHub\Integration\InstallationTokenResolver;

class InstallationClientFactory
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var InstallationTokenResolver
     */
    private $installationTokenResolver;

    /**
     * @param HttpClientInterface       $httpClient
     * @param InstallationTokenResolver $installationTokenResolver
     */
    public function __construct(HttpClientInterface $httpClient, InstallationTokenResolver $installationTokenResolver)
    {
        $this->httpClient = $httpClient;
        $this->installationTokenResolver = $installationTokenResolver;
    }

    /**
     * Create a client from an installation.
     *
     * @param Installation $installation
     *
     * @return Client
     */
    public function createClientFromInstallation(Installation $installation)
    {
        $token = $this->installationTokenResolver->get($installation);

        $client = new Client($this->httpClient);
        $client->authenticate($token->getToken(), null, Client::AUTH_HTTP_TOKEN);
        $client->setHeaders([
            'Accept' => 'application/vnd.github.machine-man-preview+json',
        ]);

        return $client;
    }
}
