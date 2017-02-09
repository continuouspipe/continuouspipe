<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Credentials;

use Firebase\ServiceAccount;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\FetchAuthTokenInterface;

final class ServiceAccountAuthTokenFetcherFactory
{
    /**
     * @var string
     */
    private $serviceAccountPath;

    public function __construct(string $serviceAccountPath)
    {
        $this->serviceAccountPath = $serviceAccountPath;
    }

    public function create() : FetchAuthTokenInterface
    {
        $serviceAccount = ServiceAccount::fromValue($this->serviceAccountPath);

        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/firebase.database',
        ];

        $credentials = [
            'client_email' => $serviceAccount->getClientEmail(),
            'client_id' => $serviceAccount->getClientId(),
            'private_key' => $serviceAccount->getPrivateKey(),
        ];

        return new ServiceAccountCredentials($scopes, $credentials);
    }
}
