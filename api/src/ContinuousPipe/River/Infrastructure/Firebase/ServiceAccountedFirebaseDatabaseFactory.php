<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Database;
use Firebase\Http\Middleware as FirebaseMiddleware;
use Firebase\V3\Auth\CustomToken;
use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

final class ServiceAccountedFirebaseDatabaseFactory implements DatabaseFactory
{
    /**
     * @var FetchAuthTokenInterface
     */
    private $authTokenFetcher;

    /**
     * @var callable
     */
    private $historyMiddleware;

    public function __construct(FetchAuthTokenInterface $authTokenFetcher, callable $historyMiddleware = null)
    {
        $this->authTokenFetcher = $authTokenFetcher;
        $this->historyMiddleware = $historyMiddleware;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $uri): Database
    {
        $stack = HandlerStack::create();
        $stack->push(FirebaseMiddleware::ensureJson(), 'ensure_json');
        $stack->push(new AuthTokenMiddleware($this->authTokenFetcher), 'auth_service_account');

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
}
