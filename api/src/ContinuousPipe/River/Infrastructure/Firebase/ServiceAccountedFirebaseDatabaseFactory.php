<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Database;
use Firebase\Http\Middleware as FirebaseMiddleware;
use Firebase\ServiceAccount;
use Firebase\V3\Auth\CustomToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

final class ServiceAccountedFirebaseDatabaseFactory implements DatabaseFactory
{
    /**
     * @var callable
     */
    private $historyMiddleware;

    /**
     * @var string
     */
    private $serviceAccountPath;

    /**
     * @param string $serviceAccountPath
     * @param callable $historyMiddleware
     */
    public function __construct(string $serviceAccountPath, callable $historyMiddleware = null)
    {
        $this->serviceAccountPath = $serviceAccountPath;
        $this->historyMiddleware = $historyMiddleware;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $uri): Database
    {
        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware(ServiceAccount::fromValue($this->serviceAccountPath));

        $stack = HandlerStack::create();
        $stack->push(FirebaseMiddleware::ensureJson(), 'ensure_json');
        $stack->push($googleAuthTokenMiddleware, 'auth_service_account');

        if (null !== $this->historyMiddleware) {
            $stack->push($this->historyMiddleware);
        }

        $http = new Client([
            'base_uri' => $uri,
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);

        $database = new Database(\GuzzleHttp\Psr7\uri_for($uri), new Database\ApiClient($http));
        $database->withCustomAuth(new CustomToken('river', [
            'system' => true,
        ]));

        return $database;
    }

    /**
     * @param ServiceAccount $serviceAccount
     *
     * @return AuthTokenMiddleware
     */
    private function createGoogleAuthTokenMiddleware(ServiceAccount $serviceAccount)
    {
        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/firebase.database',
        ];

        $credentials = [
            'client_email' => $serviceAccount->getClientEmail(),
            'client_id' => $serviceAccount->getClientId(),
            'private_key' => $serviceAccount->getPrivateKey(),
        ];

        return new AuthTokenMiddleware(new ServiceAccountCredentials($scopes, $credentials));
    }
}
