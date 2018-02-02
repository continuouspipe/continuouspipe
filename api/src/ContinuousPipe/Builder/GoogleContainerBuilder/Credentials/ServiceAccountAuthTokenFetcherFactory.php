<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder\Credentials;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\FetchAuthTokenInterface;

final class ServiceAccountAuthTokenFetcherFactory
{
    /**
     * @var string
     */
    private $serviceAccountPath;

    public function __construct(string $serviceAccountPath = null)
    {
        $this->serviceAccountPath = $serviceAccountPath;
    }

    public function create() : FetchAuthTokenInterface
    {
        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/cloud-platform',
        ];

        return new ServiceAccountCredentials($scopes, $this->serviceAccountPath);
    }
}
