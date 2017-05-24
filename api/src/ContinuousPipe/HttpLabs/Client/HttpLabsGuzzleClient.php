<?php

namespace ContinuousPipe\HttpLabs\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;

class HttpLabsGuzzleClient implements HttpLabsClient
{
    /**
     * @var HandlerStack
     */
    private $httpHandlerStack;

    /**
     * @param HandlerStack $httpHandlerStack
     */
    public function __construct(HandlerStack $httpHandlerStack)
    {
        $this->httpHandlerStack = $httpHandlerStack;
    }

    /**
     * {@inheritdoc}
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $name, string $backendUrl, array $middlewares): Stack
    {
        $httpClient = $this->createClient($apiKey);

        try {
            // Create the stack
            $response = $httpClient->request(
                'post',
                sprintf('https://api.httplabs.io/projects/%s/stacks', $projectIdentifier),
                [
                    'json' => [
                        'name' => substr($name, 0, 20),
                    ]
                ]
            );

            // Get the stack information
            $stackUri = $response->getHeaderLine('Location');
            $stackResponseContents = $httpClient->request('get', $stackUri)->getBody()->getContents();
            try {
                $responseJson = \GuzzleHttp\json_decode($stackResponseContents, true);

                if (!isset($responseJson['id']) || !isset($responseJson['url'])) {
                    throw new \InvalidArgumentException('The response needs to contain `id` and `url`');
                }
            } catch (\InvalidArgumentException $e) {
                throw new HttpLabsException('Unable to understand the response from HttpLabs', $e->getCode(), $e);
            }

            $stack = new Stack(
                $responseJson['id'],
                $responseJson['url']
            );

            $this->updateStack($apiKey, $stack->getIdentifier(), $backendUrl, $middlewares);
        } catch (RequestException $e) {
            throw new HttpLabsException('Unable to create the HttpLabs stack', $e->getCode(), $e);
        }

        return $stack;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStack(string $apiKey, string $stackIdentifier, string $backendUrl, array $middlewares)
    {
        try {
            $stackUri = 'https://api.httplabs.io/stacks/'.$stackIdentifier;
            $httpClient = $this->createClient($apiKey);
            $httpClient->request('put', $stackUri, [
                'json' => [
                    'backend' => $backendUrl,
                ]
            ]);

            if (count($middlewares) > 0) {
                // List the existing middlewares
                $existingMiddlewareUris = [];
                $middlewareListResponse = $httpClient->request('get', $stackUri . '/middlewares');
                try {
                    $responseJson = \GuzzleHttp\json_decode($middlewareListResponse->getBody()->getContents(), true);
                    if (!isset($responseJson['total'])) {
                        throw new \InvalidArgumentException('The response needs to contain `total`');
                    }

                    if ($responseJson['total'] > 0) {
                        if (!isset($responseJson['_embedded']['sp:middlewares'])) {
                            throw new \InvalidArgumentException('The response needs to contain `_embedded.sp:middlewares`');
                        }

                        $existingMiddlewareUris = array_map(function (array $middleware) {
                            return $middleware['_links']['self']['href'];
                        }, $responseJson['_embedded']['sp:middlewares']);
                    }
                } catch (\InvalidArgumentException $e) {
                    throw new HttpLabsException('Unable to understand the response from HttpLabs', $e->getCode(), $e);
                }

                // Remove the existing middlewares
                foreach ($existingMiddlewareUris as $existingMiddlewareUri) {
                    $httpClient->request('delete', $existingMiddlewareUri);
                }

                // Add the configured middlewares
                foreach ($middlewares as $middleware) {
                    $httpClient->request('post', $stackUri . '/middlewares', [
                        'json' => $middleware,
                    ]);
                }
            }

            // Deploy the stack
            $httpClient->request('post', $stackUri.'/deployments');
        } catch (RequestException $e) {
            throw new HttpLabsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $apiKey
     *
     * @return Client
     */
    private function createClient(string $apiKey): Client
    {
        $stack = clone $this->httpHandlerStack;
        $stack->push(Middleware::mapRequest(function (Request $request) use ($apiKey) {
            return $request->withAddedHeader('Authorization', $apiKey);
        }));

        return new Client([
            'handler' => $stack,
        ]);
    }

    /**
     * Delete the given stack.
     *
     * @param string $apiKey
     * @param string $stackIdentifier
     *
     * @throws HttpLabsException
     */
    public function deleteStack(string $apiKey, string $stackIdentifier)
    {
        $httpClient = $this->createClient($apiKey);

        try {
            $httpClient->request(
                'delete',
                sprintf('https://api.httplabs.io/stacks/%s', $stackIdentifier)
            );
        } catch (RequestException $e) {
            throw new HttpLabsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
