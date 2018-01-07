<?php

namespace ContinuousPipe\River\Notifications;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class NullHttpClientFactory
{
    public static function create()
    {
        $handler = HandlerStack::create(
            new MockHandler([
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
                new Response(200, ['X-Foo' => 'Bar']),
            ])
        );

        return new Client([
            'handler' => $handler,
        ]);
    }
}
