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

    public function create() : ClientInterface
    {
        $stack = HandlerStack::create();
        $stack->push(new AuthTokenMiddleware($this->authTokenFetcher), 'auth_service_account');

        if (null !== $this->historyMiddleware) {
            $stack->push($this->historyMiddleware);
        }

        return new Client([
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);
    }
}
