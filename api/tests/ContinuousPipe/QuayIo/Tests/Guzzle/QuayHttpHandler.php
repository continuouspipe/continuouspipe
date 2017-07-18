<?php

namespace ContinuousPipe\QuayIo\Tests\Guzzle;

use ContinuousPipe\River\Guzzle\MatchingHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class QuayHttpHandler extends MatchingHandler
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger, [
            [
                'match' => function (RequestInterface $request) {
                    return true;
                },
                'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    // empty
                ])),
            ]
        ]);
    }
}
