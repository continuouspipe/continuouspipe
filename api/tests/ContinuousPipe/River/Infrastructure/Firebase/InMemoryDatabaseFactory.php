<?php

namespace ContinuousPipe\River\Infrastructure\Firebase;

use Firebase\Database;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class InMemoryDatabaseFactory implements DatabaseFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(string $uri): Database
    {
        $handler = HandlerStack::create(function(RequestInterface $request, array $options) {
            return \GuzzleHttp\Promise\promise_for(
                new Response(200, ['Content-Type' => 'application/json'], '{}')
            );
        });

        $client = new Client(['handler' => $handler]);

        return new Database(
            \GuzzleHttp\Psr7\uri_for($uri),
            new Database\ApiClient($client)
        );
    }
}
