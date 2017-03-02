<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Guzzle\MatchingHandler;
use ContinuousPipe\HttpLabs\Client\Stack;
use ContinuousPipe\HttpLabs\PredictableClient;
use ContinuousPipe\HttpLabs\TraceableClient;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class HttpLabsContext implements Context
{

    /**
     * @var TraceableClient
     */
    private $traceableClient;
    /**
     * @var MatchingHandler
     */
    private $httpLabsHttpHandler;

    public function __construct(MatchingHandler $httpLabsHttpHandler, TraceableClient $traceableClient)
    {
        $this->traceableClient = $traceableClient;
        $this->httpLabsHttpHandler = $httpLabsHttpHandler;
    }

    /**
     * @Given the created HttpLabs stack will have the UUID :uuid and the URL address :url
     */
    public function theCreatedHttplabsStackWillHaveTheUuidAndTheUrlAddress($uuid, $url)
    {
        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) {
                return $request->getMethod() == 'POST' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/projects\/([^\/]+)/stacks$#i', (string) $request->getUri());
            },
            'response' => new Response(201, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Location' => 'https://api.httplabs.io/stacks/'.$uuid
            ]),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid, $url) {
                return $request->getMethod() == 'PUT' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'$#i', (string) $request->getUri());
            },
            'response' => new Response(204, ['Content-Type' => 'text/html; charset=UTF-8']),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid, $url) {
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'$#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $uuid,
                'url' => $url,
            ])),
        ]);
    }

    /**
     * @Then an HttpLabs stack should not have been created
     */
    public function anHttplabsStackShouldNotHaveBeenCreated()
    {
        $createdStacks = $this->traceableClient->getCreatedStacks();

        if (0 !== count($createdStacks)) {
            throw new \RuntimeException('Found created stacks');
        }
    }

    /**
     * @Then an HttpLabs stack should have been created with the backend :backend
     */
    public function anHttplabsStackShouldHaveBeenCreatedWithTheBackend($backend)
    {
        foreach ($this->traceableClient->getCreatedStacks() as $stack) {
            if ($stack['backend_url'] == $backend) {
                return;
            }
        }

        throw new \RuntimeException('No stack created with this backend URL');
    }
}
