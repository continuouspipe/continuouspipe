<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Guzzle\MatchingHandler;
use ContinuousPipe\HttpLabs\TraceableClient;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use GuzzleHttp\Psr7\Request;
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
    /**
     * @var History
     */
    private $httpLabsHttpHistory;

    public function __construct(MatchingHandler $httpLabsHttpHandler, TraceableClient $traceableClient, History $httpLabsHttpHistory)
    {
        $this->traceableClient = $traceableClient;
        $this->httpLabsHttpHandler = $httpLabsHttpHandler;
        $this->httpLabsHttpHistory = $httpLabsHttpHistory;
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
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'$#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'id' => $uuid,
                'url' => $url,
            ])),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid, $url) {
                return $request->getMethod() == 'DELETE' &&
                preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'$#i', (string) $request->getUri());
            },
            'response' => new Response(204),
        ]);

        $this->theHttplabsStackWillBeSuccessfullyConfigured($uuid);
    }

    /**
     * @Given the HttpLabs stack :uuid will be successfully configured
     */
    public function theHttplabsStackWillBeSuccessfullyConfigured($uuid)
    {
        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'PUT' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'$#i', (string) $request->getUri());
            },
            'response' => new Response(204, ['Content-Type' => 'text/html; charset=UTF-8']),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'POST' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'\/deployments$#i', (string) $request->getUri());
            },
            'response' => new Response(201, ['Content-Type' => 'text/html; charset=UTF-8']),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'POST' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'\/middlewares#i', (string) $request->getUri());
            },
            'response' => new Response(201, ['Content-Type' => 'text/html; charset=UTF-8']),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'DELETE' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/middlewares/([^\/]+)#i', (string) $request->getUri());
            },
            'response' => new Response(204, ['Content-Type' => 'text/html; charset=UTF-8']),
        ]);

        $this->httpLabsHttpHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'\/middlewares#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'total' => 0,
            ])),
        ]);
    }

    /**
     * @Given the HttpLabs stack :uuid have the following middlewares:
     */
    public function theHttplabsStackHaveTheFollowingMiddlewares($uuid, TableNode $table)
    {
        $middlewares = array_map(function(array $row) {
            return [
                'config' => json_decode($row['config'], true),
                '_links' => [
                    'self' => [
                        'href' => 'https://api.httplabs.io/middlewares/'.$row['identifier'],
                    ],
                    'sp:template' => [
                        'href' => $row['template'],
                    ],
                ],
            ];
        }, $table->getHash());

        $this->httpLabsHttpHandler->unshiftMatcher([
            'match' => function(RequestInterface $request) use ($uuid) {
                return $request->getMethod() == 'GET' &&
                    preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$uuid.'\/middlewares#i', (string) $request->getUri());
            },
            'response' => new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'total' => count($middlewares),
                '_embedded' => [
                    'sp:middlewares' => $middlewares,
                ]
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

    /**
     * @Then the HttpLabs stack :stackIdentifier should have been deployed
     */
    public function theHttplabsStackShouldHaveBeenDeployed($stackIdentifier)
    {
        foreach ($this->httpLabsHttpHistory as $request) {
            if  ($request->getMethod() == 'POST' && preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$stackIdentifier.'/deployments$#i', (string) $request->getUri())) {
                return;
            }
        }

        throw new \RuntimeException('The stack was not deployed');
    }


    /**
     * @Then the middleware :middlewareIdentifier from the stack :stackIdentifier should have been removed
     */
    public function theMiddlewareFromTheStackShouldHaveBeenRemoved($middlewareIdentifier, $stackIdentifier)
    {
        foreach ($this->httpLabsHttpHistory as $request) {
            if  ($request->getMethod() == 'DELETE' && preg_match('#^https\:\/\/api\.httplabs\.io\/middlewares\/'.$middlewareIdentifier.'$#i', (string) $request->getUri())) {
                return;
            }
        }

        throw new \RuntimeException('The middleware was not removed');
    }

    /**
     * @Then a middleware from the template :template should have been created on the stack :stackIdentifier with the following configuration:
     */
    public function aMiddlewareFromTheTemplateShouldHaveBeenCreatedOnTheStackWithTheFollowingConfiguration($template, $stackIdentifier, PyStringNode $string)
    {
        $expectedConfiguration = \GuzzleHttp\json_decode($string->getRaw(), true);

        foreach ($this->httpLabsHttpHistory as $request) {
            /** @var Request $request */
            if  ($request->getMethod() != 'POST' || !preg_match('#^https\:\/\/api\.httplabs\.io\/stacks\/'.$stackIdentifier.'/middlewares#i', (string) $request->getUri())) {
                continue;
            }

            $body = $request->getBody();
            $body->rewind();

            $json = \GuzzleHttp\json_decode($body->getContents(), true);

            if ($json['template'] == $template && $json['config'] == $expectedConfiguration) {
                return;
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
}
