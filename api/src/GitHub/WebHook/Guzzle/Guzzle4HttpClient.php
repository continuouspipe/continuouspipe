<?php

namespace GitHub\WebHook\Guzzle;

use Github\HttpClient\HttpClientInterface;
use Github\Client as GitHubClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Guzzle4HttpClient implements HttpClientInterface
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var array
     */
    private $defaultHeaders;

    /**
     * @var array
     */
    private $options = [
        'user_agent' => 'continuous-pipe (https://continuouspipe.github.io)',
        'api_version' => 'v3',
    ];

    /**
     * @var \Guzzle\Http\Message\Response
     */
    private $lastResponse;

    /**
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient, array $options = [])
    {
        $this->httpClient = $httpClient;
        $this->options = array_merge($this->options, $options);

        $this->defaultHeaders = [
            'Accept' => sprintf('application/vnd.github.%s+json', $this->options['api_version']),
            'User-Agent' => sprintf('%s', $this->options['user_agent']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, array $parameters = array(), array $headers = array())
    {
        $options = [
            'headers' => array_merge($this->defaultHeaders, $headers),
        ];

        if (!empty($parameters)) {
            $options['query'] = $parameters;
        }

        return $this->transformResponse(
            $this->httpClient->request('GET', $path, $options)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function post($path, $body = null, array $headers = array())
    {
        return $this->transformResponse($this->httpClient->post($path, [
            'body' => $body,
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function patch($path, $body = null, array $headers = array())
    {
        return $this->transformResponse($this->httpClient->patch($path, [
            'body' => $body,
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $body, array $headers = array())
    {
        return $this->transformResponse($this->httpClient->put($path, [
            'body' => $body,
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path, $body = null, array $headers = array())
    {
        return $this->transformResponse($this->httpClient->delete($path, [
            'body' => $body,
            'headers' => array_merge($this->defaultHeaders, $headers),
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function request($path, $body, $httpMethod = 'GET', array $headers = array(), array $options = array())
    {
        $guzzleMethod = strtolower($httpMethod);
        $guzzleOptions = [
            'body' => $body,
            'headers' => array_merge($this->defaultHeaders, $headers),
        ];

        if (array_key_exists('query', $options)) {
            $guzzleOptions['query'] = $options['query'];
        }

        $response = $this->httpClient->$guzzleMethod($path, $guzzleOptions);

        return $this->transformResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        throw new \RuntimeException('`setOption` not implemented');
    }

    /**
     * Get last response.
     *
     * This is used by the `ResultPager` object.
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers)
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($tokenOrLogin, $password, $authMethod)
    {
        if ($authMethod === GitHubClient::AUTH_HTTP_TOKEN) {
            $this->setHeaders([
                'Authorization' => sprintf('token %s', $tokenOrLogin),
            ]);
        } else {
            throw new \RuntimeException('Auth method '.$authMethod.' not supported');
        }
    }

    /**
     * Transform a Guzzle 4 response into a guzzle 3 response.
     *
     * @param Response $response
     *
     * @return \Guzzle\Http\Message\Response
     */
    private function transformResponse(Response $response)
    {
        $body = $response->getBody();
        if (null !== $body && $body->isSeekable()) {
            $body->seek(0);
        }

        $this->lastResponse = new \Guzzle\Http\Message\Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            $body ? $body->getContents() : null
        );

        return $this->lastResponse;
    }
}
