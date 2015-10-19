<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Security\Authenticator\CredentialsNotFound;
use ContinuousPipe\Security\Credentials\Bucket;
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
     * @param Bucket $bucket
     *
     * @return Client
     *
     * @throws CredentialsNotFound
     */
    public function createFromBucket(Bucket $bucket)
    {
        $tokens = $bucket->getGitHubTokens();
        if (0 === $tokens->count()) {
            throw new CredentialsNotFound(sprintf(
                'No GitHub token found in bucket "%s"',
                $bucket->getUuid()
            ));
        }

        $token = $tokens->first()->getAccessToken();
        $this->httpClient->setDefaultOption('auth', [$token, 'x-oauth-basic']);

        return $this->httpClient;
    }
}
