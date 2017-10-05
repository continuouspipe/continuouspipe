<?php

namespace Pipe;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use ContinuousPipe\Guzzle\MatchingHandler;
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
        $this->httpLabsHttpHandler->pushMatcher(
            [
                'match' => function (RequestInterface $request) {
                    return $request->getMethod() == 'POST' &&
                    preg_match(
                        '#^https\:\/\/api\.httplabs\.io\/projects\/([^\/]+)/complete-stacks$#i',
                        (string) $request->getUri()
                    );
                },
                'response' => new Response(
                    201, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                    'Location' => 'https://api.httplabs.io/stacks/' . $uuid
                ]
                ),
            ]
        );

        $this->httpLabsHttpHandler->pushMatcher(
            [
                'match' => function (RequestInterface $request) use ($uuid, $url) {
                    return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/' . $uuid . '$#i', (string) $request->getUri());
                },
                'response' => new Response(
                    200, ['Content-Type' => 'application/json'], json_encode(
                    [
                        'id' => $uuid,
                        'url' => $url,
                    ]
                )
                ),
            ]
        );

        $this->theHttplabsStackWillBeSuccessfullyConfigured($uuid);
    }

    /**
     * @Given the HttpLabs stack :uuid will be successfully configured
     */
    public function theHttplabsStackWillBeSuccessfullyConfigured($uuid)
    {
        $this->httpLabsHttpHandler->pushMatcher(
            [
                'match' => function (RequestInterface $request) use ($uuid) {
                    return $request->getMethod() == 'PUT' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/projects\/([^\/]+)/complete-stacks/' . $uuid . '$#i', (string) $request->getUri());
                },
                'response' => new Response(204, ['Content-Type' => 'text/html; charset=UTF-8']),
            ]
        );

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'DELETE' &&
                preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'$#i', (string) $request->getUri()) &&
                $request->getHeader('Authorization')[0] == 'cdba7ddb-06ac-47f8-b389-0819b48a2ee8';
            },
            'response' => new Response(204),
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

    /**
     * @Then a middleware with the name :name should have been created on the stack :stackIdentifier with the following configuration:
     */
    public function aMiddlewareWithTheNameShouldHaveBeenCreatedOnTheStackWithTheFollowingConfiguration(
        $name,
        $stackIdentifier,
        PyStringNode $string
    ) {
        $expectedConfiguration = \GuzzleHttp\json_decode($string->getRaw(), true);

        foreach ($this->traceableClient->getUpdatedStacks() as $stack) {
            if ($stack['stack_identifier'] == $stackIdentifier && (isset($stack['middlewares']))) {
                foreach ($stack['middlewares'] as $middleware) {
                    if ($middleware['name'] == $name && $middleware['config'] == $expectedConfiguration) {
                        return;
                    }
                }
            }
        }

        if (null !== $stack = $this->traceableClient->getCreatedStacks()[0]) {
            foreach ($stack['middlewares'] as $middleware) {
                if ($middleware['name'] == $name && $middleware['config'] == $expectedConfiguration) {
                    return;
                }
            }
        }

        throw new \RuntimeException('Such configured middleware not found');
    }

    /**
     * @Then the stack :stackIdentifier should have been updated
     */
    public function theStackShouldHaveBeenUpdated($stackIdentifier)
    {
        foreach ($this->traceableClient->getUpdatedStacks() as $stack) {
            if ($stack['stack_identifier'] == $stackIdentifier) {
                return;
            }
        }

        throw new \RuntimeException('The stack was not updated');
    }

    /**
     * @Then the stack :stackIdentifier should have been updated with incoming url :incoming
     */
    public function theStackShouldHaveBeenUpdatedWithIncoming($stackIdentifier, $incoming)
    {
        foreach ($this->traceableClient->getUpdatedStacks() as $stack) {
            if ($stack['stack_identifier'] == $stackIdentifier && $stack['incoming'] == $incoming) {
                return;
            }
        }
    }

        /**
     * @Then the stack :stackIdentifier should have been deleted
     */
    public function theStackShouldHaveBeenDeleted($stackIdentifier)
    {
        foreach ($this->traceableClient->getDeletedStacks() as $stack) {
            if ($stack['stack_identifier'] == $stackIdentifier) {
                return;
            }
        }

        throw new \RuntimeException('The stack was not deleted');
    }

    /**
     * @Then an HttpLabs stack should have been created with the backend :backend and incoming url :incoming
     */
    public function anHttplabsStackShouldHaveBeenCreatedWithTheBackendAndIncomingUrl($backend, $incoming)
    {
        foreach ($this->traceableClient->getCreatedStacks() as $stack) {
            if ($stack['backend_url'] == $backend && $stack['incoming'] == $incoming) {
                return;
            }
        }

        throw new \RuntimeException('No stack created with this backend URL');
    }
}
