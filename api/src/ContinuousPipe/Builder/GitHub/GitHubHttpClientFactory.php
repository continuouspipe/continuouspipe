<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\User\User;
use GuzzleHttp\Client;

class GitHubHttpClientFactory
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param User $user
     *
     * @return Client
     */
    public function createForUser(User $user)
    {
        $token = $user->getGitHubCredentials()->getAccessToken();

        $this->httpClient->setDefaultOption('auth', [$token, 'x-oauth-basic']);

        return $this->httpClient;
    }
}
