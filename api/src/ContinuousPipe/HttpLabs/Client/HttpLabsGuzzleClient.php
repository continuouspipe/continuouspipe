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
    public function createStack(string $apiKey, string $projectIdentifier, string $name, string $backendUrl, array $middlewares, string $incoming = null): Stack
    {
        $httpClient = $this->createClient($apiKey);

        try {
            // Create the stack
            $requestBody = [
                'name' => substr($name, 0, 20),
                'backend' => $backendUrl,
                'middlewares' => $middlewares
            ];
            if ($incoming !== null) {
                $requestBody['incoming'] = $incoming;
            }
            $response = $httpClient->request(
                'post',
                sprintf('https://api.httplabs.io/projects/%s/complete-stacks', $projectIdentifier),
                [
                    'json' => $requestBody
                ]
            );

            return $this->getStack($response->getHeaderLine('Location'), $httpClient);

        } catch (RequestException $e) {
            throw new HttpLabsException('Unable to create the HttpLabs stack', $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateStack(string $apiKey, string $projectIdentifier, string $stackIdentifier, string $backendUrl, array $middlewares, string $incoming = null)
    {
        try {
            $stackUri = sprintf('https://api.httplabs.io/projects/%s/complete-stacks/%s', $projectIdentifier, $stackIdentifier);
            $httpClient = $this->createClient($apiKey);
            $httpClient->request('put', $stackUri, [
                'json' => [
                    'backend' => $backendUrl,
                    'middlewares' => $middlewares,
                    'incoming' => $incoming,
                ]
            ]);

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

        return new Client(['handler' => $stack]);
    }

    private function getStack(string $stackUri, Client $httpClient): Stack
    {
        $stackResponseContents = $httpClient->request('get', $stackUri)->getBody()->getContents();
        try {
            $responseJson = \GuzzleHttp\json_decode($stackResponseContents, true);

            if (!isset($responseJson['id']) || !isset($responseJson['url'])) {
                throw new \InvalidArgumentException('The response needs to contain `id` and `url`');
            }
        } catch (\InvalidArgumentException $e) {
            throw new HttpLabsException('Unable to understand the response from HttpLabs', $e->getCode(), $e);
        }

        return new Stack($responseJson['id'], $responseJson['url']);
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
