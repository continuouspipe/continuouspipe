<?php

namespace ContinuousPipe\River\Notifications;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Mock;

class NullHttpClientFactory
{
    public static function create()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(200, ['X-Foo' => 'Bar']),
        ]);

        // Add the mock subscriber to the client.
        $client->getEmitter()->attach($mock);

        return $client;
    }
}
