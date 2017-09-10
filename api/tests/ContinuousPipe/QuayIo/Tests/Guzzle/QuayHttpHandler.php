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
            // Create a Robot
            [
                'match' => function (RequestInterface $request) {
                    return
                        $request->getMethod() == 'PUT' &&
                        preg_match('#/organization/([a-z0-9-]+)/robots/([a-z0-9-]+)$#', $request->getUri());
                },
                'response' => function(RequestInterface $request) {
                    preg_match('#/organization/([a-z0-9-]+)/robots/([a-z0-9-]+)$#', $request->getUri(), $matches);

                    return new Response(201, ['Content-Type' => 'application/json'], json_encode([
                        'name' => $matches[1].'+'.$matches[2],
                        'token' => 'thisisatoken',
                    ]));
                },
            ],

            // Create a repository
            [
                'match' => function (RequestInterface $request) {
                    return
                        $request->getMethod() == 'POST' && preg_match('#/repository$#', $request->getUri());
                },
                'response' => function(RequestInterface $request) {
                    $jsonBody = \GuzzleHttp\json_decode($request->getBody()->getContents(), true);

                    return new Response(201, ['Content-Type' => 'application/json'], json_encode([
                        'kind' => 'image',
                        'namespace' => $jsonBody['namespace'],
                        'name' => $jsonBody['repository'],
                    ]));
                },
            ],

            // Grant a robot access
            [
                'match' => function (RequestInterface $request) {
                    return
                        $request->getMethod() == 'PUT' &&
                        preg_match('#/repository/([a-z0-9\/-]+)/permissions/user/([a-z0-9\+-]+)$#', $request->getUri());
                },
                'response' => function(RequestInterface $request) {
                    preg_match('#/repository/([a-z0-9\/-]+)/permissions/user/([a-z0-9\+-]+)$#', $request->getUri(), $matches);

                    return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                        'name' => $matches[2],
                        'is_org_member' => true,
                        'is_robot' => true,
                        'role' => 'write',
                    ]));
                },
            ],
        ]);
    }
}
