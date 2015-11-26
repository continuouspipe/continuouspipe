<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use Github\HttpClient\HttpClientInterface;

class TestHttpClient implements HttpClientInterface
{
    /**
     * @var array
     */
    private $requests = [];

    /**
     * {@inheritDoc}
     */
    public function get($path, array $parameters = array(), array $headers = array())
    {
        return $this->request($path, null, 'GET', $headers, array('query' => $parameters));
    }

    /**
     * {@inheritDoc}
     */
    public function post($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function patch($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'PATCH', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'DELETE', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function put($path, $body, array $headers = array())
    {
        return $this->request($path, $body, 'PUT', $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function request($path, $body, $httpMethod = 'GET', array $headers = array())
    {
        $this->requests[] = [
            'path' => $path,
            'method' => $httpMethod,
            'body' => $body,
        ];

        return new \Guzzle\Http\Message\Response(
            200,
            [],
            ''
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($tokenOrLogin, $password, $authMethod)
    {
    }

    /**
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
