<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder\Credentials;

use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;

class GuzzleHttpClientFactory
{
    /**
     * @var callable
     */
    private $historyMiddleware;

    /**
     * @var string
     */
    private $serviceAccountPath;

    public function __construct(callable $historyMiddleware = null, string $serviceAccountPath = null)
    {
        $this->historyMiddleware = $historyMiddleware;
        $this->serviceAccountPath = $serviceAccountPath;
    }

    public function create() : ClientInterface
    {
        $stack = HandlerStack::create();
        $stack->push(new AuthTokenMiddleware(
            (new ServiceAccountAuthTokenFetcherFactory($this->serviceAccountPath))->create()
        ), 'auth_service_account');

        if (null !== $this->historyMiddleware) {
            $stack->push($this->historyMiddleware);
        }

        return new Client([
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);
    }
}
